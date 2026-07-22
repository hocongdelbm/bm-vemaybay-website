<?php

use PHPUnit\Framework\TestCase;

if (!defined('sugarEntry')) {
    define('sugarEntry', true);
}

$entryImageUploadWebhookLibraryOnly = true;
require_once dirname(__DIR__, 5) . '/custom/entrypoints/entryImageUploadWebhook.php';
unset($entryImageUploadWebhookLibraryOnly);

final class NextCloudImageUploadFakeApi
{
    public $calls = [];
    public $folderResponses = [];
    public $uploadResponses = [];
    public $shareResponses = [];
    public $getSharesResponses = [];
    public $deleteShareResponses = [];
    public $deleteFileResponses = [];

    private $shareCallCount = 0;

    public function createFolder($path)
    {
        $this->record('createFolder', [$path]);

        return $this->nextResponse(
            $this->folderResponses,
            ['status' => 1, 'httpCode' => 201],
            [$path]
        );
    }

    public function uploadFile($localPath, $remotePath, array $options = [])
    {
        $this->record('uploadFile', [$localPath, $remotePath, $options]);

        return $this->nextResponse(
            $this->uploadResponses,
            ['status' => 1, 'httpCode' => 201],
            [$localPath, $remotePath, $options]
        );
    }

    public function createShare($remotePath, $permissions = 1)
    {
        $this->shareCallCount++;
        $this->record('createShare', [$remotePath, $permissions]);

        $shareId = 'share-' . $this->shareCallCount;
        return $this->nextResponse(
            $this->shareResponses,
            [
                'status' => 1,
                'httpCode' => 200,
                'data' => [
                    'id' => $shareId,
                    'url' => 'https://nextcloud.example.com/s/' . $shareId,
                ],
            ],
            [$remotePath, $permissions]
        );
    }

    public function getShares($remotePath)
    {
        $this->record('getShares', [$remotePath]);

        return $this->nextResponse(
            $this->getSharesResponses,
            ['status' => 1, 'httpCode' => 200, 'data' => []],
            [$remotePath]
        );
    }

    public function deleteShare($shareId)
    {
        $this->record('deleteShare', [$shareId]);

        return $this->nextResponse(
            $this->deleteShareResponses,
            ['status' => 1, 'httpCode' => 200],
            [$shareId]
        );
    }

    public function deleteFile($remotePath)
    {
        $this->record('deleteFile', [$remotePath]);

        return $this->nextResponse(
            $this->deleteFileResponses,
            ['status' => 1, 'httpCode' => 204],
            [$remotePath]
        );
    }

    public function callsFor($method)
    {
        return array_values(array_filter($this->calls, static function (array $call) use ($method) {
            return $call['method'] === $method;
        }));
    }

    private function record($method, array $arguments)
    {
        $this->calls[] = [
            'method' => $method,
            'arguments' => $arguments,
        ];
    }

    private function nextResponse(array &$responses, array $default, array $arguments)
    {
        $response = count($responses) > 0 ? array_shift($responses) : $default;
        if ($response instanceof Closure) {
            return call_user_func_array($response, $arguments);
        }

        return $response;
    }
}

final class NextCloudImageUploadFakeLogger
{
    public $warnings = [];

    public function warn($message)
    {
        $this->warnings[] = (string) $message;
    }
}

final class NextCloudImageUploadServiceTest extends TestCase
{
    private $temporaryDirectory;
    private $temporaryFileCounter = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->temporaryDirectory = sys_get_temp_dir()
            . '/nextcloud-image-upload-service-test-'
            . str_replace('.', '', uniqid('', true));
        if (!mkdir($this->temporaryDirectory, 0700, true) && !is_dir($this->temporaryDirectory)) {
            self::fail('Unable to create the temporary test directory');
        }
    }

    protected function tearDown(): void
    {
        if (is_dir($this->temporaryDirectory)) {
            $entries = scandir($this->temporaryDirectory);
            if (is_array($entries)) {
                foreach ($entries as $entry) {
                    if ($entry === '.' || $entry === '..') {
                        continue;
                    }
                    $path = $this->temporaryDirectory . '/' . $entry;
                    if (is_file($path)) {
                        unlink($path);
                    }
                }
            }
            rmdir($this->temporaryDirectory);
        }

        parent::tearDown();
    }

    public function testUploadsJpegAndPngInInputOrderWithDeterministicMetadataAndPaths(): void
    {
        $jpegPath = $this->createImage('jpeg', 4, 3);
        $pngPath = $this->createImage('png', 3, 2);
        $api = new NextCloudImageUploadFakeApi();
        $api->folderResponses = [
            ['status' => 1, 'httpCode' => 201],
            ['status' => 1, 'httpCode' => 405],
            ['status' => 1, 'httpCode' => 201],
            ['status' => 1, 'httpCode' => 405],
            ['status' => 1, 'httpCode' => 201],
        ];
        $service = $this->createService($api);

        $result = $service->uploadFiles($this->createFilesField([
            ['path' => $jpegPath, 'name' => 'client-called-this.png'],
            ['path' => $pngPath, 'name' => 'second-photo.jpg'],
        ]));

        self::assertCount(2, $result);
        self::assertSame([
            'index' => 0,
            'url' => 'https://nextcloud.example.com/s/share-1/preview',
            'shareUrl' => 'https://nextcloud.example.com/s/share-1',
            'originalName' => 'client-called-this.png',
            'mimeType' => 'image/jpeg',
            'extension' => 'jpg',
            'size' => filesize($jpegPath),
            'width' => 4,
            'height' => 3,
            'sha256' => hash_file('sha256', $jpegPath),
        ], $result[0]);
        self::assertSame([
            'index' => 1,
            'url' => 'https://nextcloud.example.com/s/share-2/preview',
            'shareUrl' => 'https://nextcloud.example.com/s/share-2',
            'originalName' => 'second-photo.jpg',
            'mimeType' => 'image/png',
            'extension' => 'png',
            'size' => filesize($pngPath),
            'width' => 3,
            'height' => 2,
            'sha256' => hash_file('sha256', $pngPath),
        ], $result[1]);

        self::assertSame(
            [
                '/bmvmb',
                '/bmvmb/chat_uploads',
                '/bmvmb/chat_uploads/2026',
                '/bmvmb/chat_uploads/2026/07',
                '/bmvmb/chat_uploads/2026/07/03',
            ],
            $this->firstArguments($api->callsFor('createFolder'))
        );

        $uploadCalls = $api->callsFor('uploadFile');
        self::assertSame(
            [
                $jpegPath,
                '/bmvmb/chat_uploads/2026/07/03/fixture1_1783012345.jpg',
                ['prevent_overwrite' => true],
            ],
            $uploadCalls[0]['arguments']
        );
        self::assertSame(
            [
                $pngPath,
                '/bmvmb/chat_uploads/2026/07/03/fixture2_1783012346.png',
                ['prevent_overwrite' => true],
            ],
            $uploadCalls[1]['arguments']
        );
        self::assertSame(
            [
                ['/bmvmb/chat_uploads/2026/07/03/fixture1_1783012345.jpg', 1],
                ['/bmvmb/chat_uploads/2026/07/03/fixture2_1783012346.png', 1],
            ],
            array_column($api->callsFor('createShare'), 'arguments')
        );
    }

    public function testPreparesWebpWhenRuntimeSupportsIt(): void
    {
        if (!defined('IMAGETYPE_WEBP')) {
            self::markTestSkipped('The current PHP runtime does not define IMAGETYPE_WEBP');
        }

        $webpBytes = base64_decode(
            'UklGRj4AAABXRUJQVlA4IDIAAAAQAgCdASoFAAQAAMASJaACdLoB+AH4gAPIAP762j//ftL4E7xn/q5/9jM3RJf7+QAAAA==',
            true
        );
        self::assertIsString($webpBytes);
        $webpPath = $this->createRawFile($webpBytes);
        $webpInfo = @getimagesize($webpPath);
        if (!is_array($webpInfo) || (int) ($webpInfo[2] ?? 0) !== constant('IMAGETYPE_WEBP')) {
            self::markTestSkipped('The current PHP runtime cannot inspect WebP dimensions');
        }

        $api = new NextCloudImageUploadFakeApi();
        $service = $this->createService($api);

        $uploaded = $service->uploadFiles($this->createFilesField([
            ['path' => $webpPath, 'name' => 'photo.jpeg'],
        ]));

        self::assertCount(1, $uploaded);
        self::assertSame('image/webp', $uploaded[0]['mimeType']);
        self::assertSame('webp', $uploaded[0]['extension']);
        self::assertSame('photo.jpeg', $uploaded[0]['originalName']);
        self::assertSame(5, $uploaded[0]['width']);
        self::assertSame(4, $uploaded[0]['height']);
        self::assertSame(filesize($webpPath), $uploaded[0]['size']);
        self::assertSame(hash_file('sha256', $webpPath), $uploaded[0]['sha256']);
        self::assertSame(
            '/bmvmb/chat_uploads/2026/07/03/fixture1_1783012345.webp',
            $api->callsFor('uploadFile')[0]['arguments'][1]
        );
    }

    public function testBuildCanonicalManifestIsExactAndPreservesFileOrder(): void
    {
        $prepared = [
            ['size' => 123, 'sha256' => str_repeat('a', 64)],
            ['size' => 456, 'sha256' => str_repeat('b', 64)],
        ];

        $manifest = NextCloudImageUploadService::buildCanonicalManifest(
            'chat_websocket',
            '018f6c2a-1234-7000-8000-123456789abc',
            '1783012345123',
            $prepared
        );

        self::assertSame(
            "image-upload-v1\n"
            . "chat_websocket\n"
            . "018f6c2a-1234-7000-8000-123456789abc\n"
            . "1783012345123\n"
            . "2\n"
            . '0:123:' . str_repeat('a', 64) . "\n"
            . '1:456:' . str_repeat('b', 64),
            $manifest
        );
        self::assertFalse(substr($manifest, -1) === "\n", 'Manifest must not end in a newline');

        $reversed = NextCloudImageUploadService::buildCanonicalManifest(
            'chat_websocket',
            '018f6c2a-1234-7000-8000-123456789abc',
            '1783012345123',
            array_reverse($prepared)
        );
        self::assertNotSame($manifest, $reversed);
        self::assertStringContainsString('0:456:' . str_repeat('b', 64), $reversed);
        self::assertStringContainsString('1:123:' . str_repeat('a', 64), $reversed);
    }

    public function testRetryOfSameImageCreatesANewRemotePathAndUrl(): void
    {
        $jpegPath = $this->createImage('jpeg', 2, 2);
        $api = new NextCloudImageUploadFakeApi();
        $service = $this->createService($api);
        $filesField = $this->createFilesField([
            ['path' => $jpegPath, 'name' => 'same.jpg'],
        ]);

        $first = $service->uploadFiles($filesField);
        $second = $service->uploadFiles($filesField);

        self::assertNotSame($first[0]['url'], $second[0]['url']);
        self::assertSame($first[0]['sha256'], $second[0]['sha256']);
        self::assertSame([
            '/bmvmb/chat_uploads/2026/07/03/fixture1_1783012345.jpg',
            '/bmvmb/chat_uploads/2026/07/03/fixture2_1783012346.jpg',
        ], array_map(static function (array $call) {
            return $call['arguments'][1];
        }, $api->callsFor('uploadFile')));
        self::assertCount(0, $api->callsFor('getShares'), 'Successful retries must not deduplicate by share lookup');
    }

    public function testRejectsGif(): void
    {
        $gifPath = $this->createImage('gif', 2, 2);
        $service = $this->createService(new NextCloudImageUploadFakeApi());

        $this->assertUploadException(415, 'UNSUPPORTED_IMAGE_TYPE', static function () use ($service, $gifPath) {
            $service->validateAndPrepareFiles([
                'name' => ['image.gif'],
                'tmp_name' => [$gifPath],
                'error' => [UPLOAD_ERR_OK],
                'size' => [filesize($gifPath)],
            ]);
        });
    }

    public function testRejectsSvgAndPdf(): void
    {
        $service = $this->createService(new NextCloudImageUploadFakeApi());
        $fixtures = [
            ['name' => 'vector.svg', 'contents' => '<svg xmlns="http://www.w3.org/2000/svg" width="2" height="2"></svg>'],
            ['name' => 'document.pdf', 'contents' => "%PDF-1.4\n1 0 obj\n<<>>\nendobj\n%%EOF"],
        ];

        foreach ($fixtures as $fixture) {
            $path = $this->createRawFile($fixture['contents']);
            $this->assertUploadException(415, 'UNSUPPORTED_IMAGE_TYPE', function () use (
                $service,
                $path,
                $fixture
            ) {
                $service->validateAndPrepareFiles($this->createFilesField([
                    ['path' => $path, 'name' => $fixture['name']],
                ]));
            });
        }
    }

    public function testAcceptsLegacyScalarUploadShape(): void
    {
        $jpegPath = $this->createImage('jpeg', 3, 2);
        $service = $this->createService(new NextCloudImageUploadFakeApi());

        $prepared = $service->validateAndPrepareFiles([
            'name' => 'legacy-client.png',
            'tmp_name' => $jpegPath,
            'error' => UPLOAD_ERR_OK,
            'size' => 1,
        ]);

        self::assertCount(1, $prepared);
        self::assertSame(0, $prepared[0]['index']);
        self::assertSame('legacy-client.png', $prepared[0]['originalName']);
        self::assertSame('image/jpeg', $prepared[0]['mimeType']);
        self::assertSame('jpg', $prepared[0]['extension']);
        self::assertSame(filesize($jpegPath), $prepared[0]['size']);
    }

    public function testRejectsCorruptedJpegContent(): void
    {
        $validPath = $this->createImage('jpeg', 3, 2);
        $contents = file_get_contents($validPath);
        self::assertIsString($contents);
        $brokenPath = $this->createRawFile(substr($contents, 0, 16));

        $detectedMime = (new finfo(FILEINFO_MIME_TYPE))->file($brokenPath);
        self::assertSame('image/jpeg', $detectedMime, 'Broken fixture must still be detected as JPEG');
        self::assertFalse(@getimagesize($brokenPath), 'Broken fixture must not contain valid image dimensions');

        $service = $this->createService(new NextCloudImageUploadFakeApi());
        $this->assertUploadException(415, 'INVALID_IMAGE', function () use ($service, $brokenPath) {
            $service->validateAndPrepareFiles($this->createFilesField([
                ['path' => $brokenPath, 'name' => 'broken.jpg'],
            ]));
        });
    }

    public function testRejectsEmptyImage(): void
    {
        $emptyPath = $this->createRawFile('');
        $service = $this->createService(new NextCloudImageUploadFakeApi());

        $this->assertUploadException(400, 'INVALID_IMAGE', function () use ($service, $emptyPath) {
            $service->validateAndPrepareFiles($this->createFilesField([
                ['path' => $emptyPath, 'name' => 'empty.jpg'],
            ]));
        });
    }

    public function testRejectsImageOverFiveMebibytesUsingActualFileSize(): void
    {
        $largePath = $this->nextTemporaryPath('large.bin');
        $handle = fopen($largePath, 'wb');
        self::assertIsResource($handle);
        self::assertSame(0, fseek($handle, NextCloudImageUploadService::MAX_FILE_SIZE));
        self::assertSame(1, fwrite($handle, "\0"));
        fclose($handle);
        clearstatcache(true, $largePath);
        self::assertSame(NextCloudImageUploadService::MAX_FILE_SIZE + 1, filesize($largePath));

        $service = $this->createService(new NextCloudImageUploadFakeApi());
        $this->assertUploadException(413, 'FILE_TOO_LARGE', function () use ($service, $largePath) {
            $service->validateAndPrepareFiles([
                'name' => ['large.jpg'],
                'tmp_name' => [$largePath],
                'error' => [UPLOAD_ERR_OK],
                'size' => [1],
            ]);
        });
    }

    public function testAcceptsFiveImagesAtExactPerFileAndBatchLimits(): void
    {
        $fixtures = [];
        for ($index = 0; $index < NextCloudImageUploadService::MAX_FILE_COUNT; $index++) {
            $path = $this->createImage('jpeg', 2, 2);
            $handle = fopen($path, 'ab');
            self::assertIsResource($handle);
            self::assertTrue(ftruncate($handle, NextCloudImageUploadService::MAX_FILE_SIZE));
            fclose($handle);
            clearstatcache(true, $path);
            self::assertSame(NextCloudImageUploadService::MAX_FILE_SIZE, filesize($path));
            $fixtures[] = ['path' => $path, 'name' => "image-{$index}.jpg"];
        }

        $api = new NextCloudImageUploadFakeApi();
        $service = $this->createService($api);
        $files = $service->uploadFiles($this->createFilesField($fixtures));

        self::assertCount(NextCloudImageUploadService::MAX_FILE_COUNT, $files);
        self::assertSame(
            NextCloudImageUploadService::MAX_BATCH_SIZE,
            array_sum(array_column($files, 'size'))
        );
        self::assertCount(NextCloudImageUploadService::MAX_FILE_COUNT, $api->callsFor('uploadFile'));
        self::assertCount(NextCloudImageUploadService::MAX_FILE_COUNT, $api->callsFor('createShare'));
    }

    public function testRejectsMoreThanFiveFilesBeforeReadingAnyFile(): void
    {
        $service = $this->createService(new NextCloudImageUploadFakeApi());
        $field = [
            'name' => [],
            'tmp_name' => [],
            'error' => [],
            'size' => [],
        ];
        for ($index = 0; $index < 6; $index++) {
            $field['name'][] = 'image-' . $index . '.jpg';
            $field['tmp_name'][] = '/path/does/not/matter-' . $index;
            $field['error'][] = UPLOAD_ERR_OK;
            $field['size'][] = 1;
        }

        $this->assertUploadException(413, 'TOO_MANY_FILES', static function () use ($service, $field) {
            $service->validateAndPrepareFiles($field);
        });
    }

    public function testValidatesWholeBatchBeforeWritingToNextCloud(): void
    {
        $jpegPath = $this->createImage('jpeg', 2, 2);
        $gifPath = $this->createImage('gif', 2, 2);
        $api = new NextCloudImageUploadFakeApi();
        $service = $this->createService($api);

        $this->assertUploadException(415, 'UNSUPPORTED_IMAGE_TYPE', function () use ($service, $jpegPath, $gifPath) {
            $service->uploadFiles($this->createFilesField([
                ['path' => $jpegPath, 'name' => 'valid.jpg'],
                ['path' => $gifPath, 'name' => 'invalid.gif'],
            ]));
        });

        self::assertSame([], $api->calls);
    }

    public function testFolderCreationRejectsHttpCodesOtherThan201And405(): void
    {
        $jpegPath = $this->createImage('jpeg', 2, 2);
        $api = new NextCloudImageUploadFakeApi();
        $api->folderResponses = [
            ['status' => 1, 'httpCode' => 200],
        ];
        $service = $this->createService($api);

        $this->assertUploadException(502, 'NEXTCLOUD_FOLDER_FAILED', function () use ($service, $jpegPath) {
            $service->uploadFiles($this->createFilesField([
                ['path' => $jpegPath, 'name' => 'image.jpg'],
            ]));
        });

        self::assertSame(['/bmvmb'], $this->firstArguments($api->callsFor('createFolder')));
        self::assertCount(0, $api->callsFor('uploadFile'));
    }

    public function testFolderCreationRequiresSuccessfulStatusForHttp201(): void
    {
        $jpegPath = $this->createImage('jpeg', 2, 2);
        $api = new NextCloudImageUploadFakeApi();
        $api->folderResponses = [
            ['status' => 0, 'httpCode' => 201],
        ];
        $service = $this->createService($api);

        $this->assertUploadException(502, 'NEXTCLOUD_FOLDER_FAILED', function () use ($service, $jpegPath) {
            $service->uploadFiles($this->createFilesField([
                ['path' => $jpegPath, 'name' => 'image.jpg'],
            ]));
        });

        self::assertCount(0, $api->callsFor('uploadFile'));
    }

    public function testUploadRetriesWithANewPathWhenConditionalPutReportsCollision(): void
    {
        $jpegPath = $this->createImage('jpeg', 2, 2);
        $api = new NextCloudImageUploadFakeApi();
        $api->uploadResponses = [
            ['status' => 0, 'httpCode' => 412],
            ['status' => 1, 'httpCode' => 201],
        ];
        $service = $this->createService($api);

        $files = $service->uploadFiles($this->createFilesField([
            ['path' => $jpegPath, 'name' => 'image.jpg'],
        ]));

        self::assertCount(1, $files);
        self::assertSame([
            '/bmvmb/chat_uploads/2026/07/03/fixture1_1783012345.jpg',
            '/bmvmb/chat_uploads/2026/07/03/fixture2_1783012346.jpg',
        ], array_map(static function (array $call) {
            return $call['arguments'][1];
        }, $api->callsFor('uploadFile')));
        self::assertSame(
            '/bmvmb/chat_uploads/2026/07/03/fixture2_1783012346.jpg',
            $api->callsFor('createShare')[0]['arguments'][0]
        );
        self::assertCount(0, $api->callsFor('deleteFile'));
        self::assertCount(0, $api->callsFor('deleteShare'));
    }

    public function testUploadStopsAfterRepeatedPathCollisionsWithoutDeletingExistingFiles(): void
    {
        $jpegPath = $this->createImage('jpeg', 2, 2);
        $api = new NextCloudImageUploadFakeApi();
        $api->uploadResponses = array_fill(0, 3, ['status' => 0, 'httpCode' => 412]);
        $service = $this->createService($api);

        $this->assertUploadException(502, 'NEXTCLOUD_UPLOAD_COLLISION', function () use ($service, $jpegPath) {
            $service->uploadFiles($this->createFilesField([
                ['path' => $jpegPath, 'name' => 'image.jpg'],
            ]));
        });

        self::assertCount(3, $api->callsFor('uploadFile'));
        self::assertCount(0, $api->callsFor('createShare'));
        self::assertCount(0, $api->callsFor('deleteFile'));
        self::assertCount(0, $api->callsFor('deleteShare'));
    }

    public function testSecondUploadFailureRollsBackCreatedShareBeforeItsFile(): void
    {
        $firstPath = $this->createImage('jpeg', 2, 2);
        $secondPath = $this->createImage('png', 2, 2);
        $api = new NextCloudImageUploadFakeApi();
        $api->uploadResponses = [
            ['status' => 1, 'httpCode' => 201],
            ['status' => 0, 'httpCode' => 500],
        ];
        $service = $this->createService($api);

        $this->assertUploadException(502, 'NEXTCLOUD_UPLOAD_FAILED', function () use (
            $service,
            $firstPath,
            $secondPath
        ) {
            $service->uploadFiles($this->createFilesField([
                ['path' => $firstPath, 'name' => 'first.jpg'],
                ['path' => $secondPath, 'name' => 'second.png'],
            ]));
        });

        $firstRemotePath = '/bmvmb/chat_uploads/2026/07/03/fixture1_1783012345.jpg';
        $secondRemotePath = '/bmvmb/chat_uploads/2026/07/03/fixture2_1783012346.png';
        self::assertSame([$secondRemotePath], $this->firstArguments($api->callsFor('getShares')));
        self::assertSame(['share-1'], $this->firstArguments($api->callsFor('deleteShare')));
        self::assertSame(
            [$secondRemotePath, $firstRemotePath],
            $this->firstArguments($api->callsFor('deleteFile'))
        );

        $shareDeleteIndex = $this->findCallIndex($api, 'deleteShare', 'share-1');
        $firstFileDeleteIndex = $this->findCallIndex($api, 'deleteFile', $firstRemotePath);
        self::assertLessThan($firstFileDeleteIndex, $shareDeleteIndex, 'Share must be removed before its file');
    }

    public function testRollbackLogsCleanupResponseFailuresWithoutMaskingOriginalUploadError(): void
    {
        $firstPath = $this->createImage('jpeg', 2, 2);
        $secondPath = $this->createImage('png', 2, 2);
        $api = new NextCloudImageUploadFakeApi();
        $api->uploadResponses = [
            ['status' => 1, 'httpCode' => 201],
            ['status' => 0, 'httpCode' => 500],
        ];
        $api->deleteShareResponses = [
            ['status' => 0, 'httpCode' => 500],
        ];
        $api->deleteFileResponses = [
            ['status' => 0, 'httpCode' => 500],
            ['status' => 0, 'httpCode' => 500],
        ];
        $service = $this->createService($api);
        $logger = new NextCloudImageUploadFakeLogger();
        $hadOriginalLogger = array_key_exists('log', $GLOBALS);
        $originalLogger = $GLOBALS['log'] ?? null;
        $GLOBALS['log'] = $logger;

        try {
            $this->assertUploadException(502, 'NEXTCLOUD_UPLOAD_FAILED', function () use (
                $service,
                $firstPath,
                $secondPath
            ) {
                $service->uploadFiles($this->createFilesField([
                    ['path' => $firstPath, 'name' => 'first.jpg'],
                    ['path' => $secondPath, 'name' => 'second.png'],
                ]));
            });
        } finally {
            if ($hadOriginalLogger) {
                $GLOBALS['log'] = $originalLogger;
            } else {
                unset($GLOBALS['log']);
            }
        }

        self::assertCount(3, $logger->warnings);
        self::assertStringContainsString('file', $logger->warnings[0]);
        self::assertStringContainsString('share', $logger->warnings[1]);
        self::assertStringContainsString('file', $logger->warnings[2]);
    }

    public function testShareFailureLooksUpAndDeletesOrphanShareBeforeDeletingFile(): void
    {
        $jpegPath = $this->createImage('jpeg', 2, 2);
        $api = new NextCloudImageUploadFakeApi();
        $api->shareResponses = [
            ['status' => 0, 'httpCode' => 500],
        ];
        $api->getSharesResponses = [
            ['status' => 1, 'httpCode' => 200, 'data' => [['id' => 'orphan-share']]],
        ];
        $service = $this->createService($api);

        $this->assertUploadException(502, 'NEXTCLOUD_SHARE_FAILED', function () use ($service, $jpegPath) {
            $service->uploadFiles($this->createFilesField([
                ['path' => $jpegPath, 'name' => 'image.jpg'],
            ]));
        });

        $remotePath = '/bmvmb/chat_uploads/2026/07/03/fixture1_1783012345.jpg';
        self::assertSame([$remotePath], $this->firstArguments($api->callsFor('getShares')));
        self::assertSame(['orphan-share'], $this->firstArguments($api->callsFor('deleteShare')));
        self::assertSame([$remotePath], $this->firstArguments($api->callsFor('deleteFile')));
        self::assertLessThan(
            $this->findCallIndex($api, 'deleteFile', $remotePath),
            $this->findCallIndex($api, 'deleteShare', 'orphan-share')
        );
    }

    public function testRejectsNonHttpsShareUrlAndRollsBackUploadedFile(): void
    {
        $jpegPath = $this->createImage('jpeg', 2, 2);
        $api = new NextCloudImageUploadFakeApi();
        $api->shareResponses = [[
            'status' => 1,
            'httpCode' => 200,
            'data' => [
                'id' => 'unsafe-share',
                'url' => 'http://nextcloud.example.com/s/unsafe-share',
            ],
        ]];
        $service = $this->createService($api);

        $this->assertUploadException(502, 'NEXTCLOUD_SHARE_FAILED', function () use ($service, $jpegPath) {
            $service->uploadFiles($this->createFilesField([
                ['path' => $jpegPath, 'name' => 'image.jpg'],
            ]));
        });

        self::assertSame(
            ['/bmvmb/chat_uploads/2026/07/03/fixture1_1783012345.jpg'],
            $this->firstArguments($api->callsFor('deleteFile'))
        );
    }

    public function testMissingNextCloudConfigurationFailsClosedBeforeNetworkClientCreation(): void
    {
        $jpegPath = $this->createImage('jpeg', 2, 2);
        $hadOriginalConfig = array_key_exists('sugar_config', $GLOBALS);
        $originalConfig = $GLOBALS['sugar_config'] ?? null;
        $GLOBALS['sugar_config'] = [];
        $service = new NextCloudImageUploadService(null, [
            'uploaded_file_validator' => static function () {
                return true;
            },
        ]);

        try {
            $this->assertUploadException(500, 'CONFIGURATION_ERROR', function () use ($service, $jpegPath) {
                $service->uploadFiles($this->createFilesField([
                    ['path' => $jpegPath, 'name' => 'image.jpg'],
                ]));
            });
        } finally {
            if ($hadOriginalConfig) {
                $GLOBALS['sugar_config'] = $originalConfig;
            } else {
                unset($GLOBALS['sugar_config']);
            }
        }
    }

    public function testFolderTimeoutMapsToGatewayTimeout(): void
    {
        $jpegPath = $this->createImage('jpeg', 2, 2);
        $api = new NextCloudImageUploadFakeApi();
        $api->folderResponses = [
            ['status' => 0, 'httpCode' => 504, 'timedOut' => true],
        ];
        $service = $this->createService($api);

        $this->assertUploadException(504, 'UPLOAD_TIMEOUT', function () use ($service, $jpegPath) {
            $service->uploadFiles($this->createFilesField([
                ['path' => $jpegPath, 'name' => 'image.jpg'],
            ]));
        });
        self::assertCount(0, $api->callsFor('uploadFile'));
    }

    public function testUploadTimeoutMapsToGatewayTimeoutAndRollsBackFile(): void
    {
        $jpegPath = $this->createImage('jpeg', 2, 2);
        $api = new NextCloudImageUploadFakeApi();
        $api->uploadResponses = [
            ['status' => 0, 'httpCode' => 504, 'timedOut' => true],
        ];
        $service = $this->createService($api);

        $this->assertUploadException(504, 'UPLOAD_TIMEOUT', function () use ($service, $jpegPath) {
            $service->uploadFiles($this->createFilesField([
                ['path' => $jpegPath, 'name' => 'image.jpg'],
            ]));
        });

        self::assertSame(
            ['/bmvmb/chat_uploads/2026/07/03/fixture1_1783012345.jpg'],
            $this->firstArguments($api->callsFor('deleteFile'))
        );
    }

    public function testShareTimeoutMapsToGatewayTimeoutAndRollsBackFile(): void
    {
        $jpegPath = $this->createImage('jpeg', 2, 2);
        $api = new NextCloudImageUploadFakeApi();
        $api->shareResponses = [
            ['status' => 0, 'httpCode' => 504, 'timedOut' => true],
        ];
        $service = $this->createService($api);

        $this->assertUploadException(504, 'UPLOAD_TIMEOUT', function () use ($service, $jpegPath) {
            $service->uploadFiles($this->createFilesField([
                ['path' => $jpegPath, 'name' => 'image.jpg'],
            ]));
        });

        self::assertSame(
            ['/bmvmb/chat_uploads/2026/07/03/fixture1_1783012345.jpg'],
            $this->firstArguments($api->callsFor('deleteFile'))
        );
    }

    public function testLocalOperationDeadlineMapsToGatewayTimeoutBeforeNetworkCall(): void
    {
        $jpegPath = $this->createImage('jpeg', 2, 2);
        $clockValues = [1000, 23000];
        $lastClockValue = 23000;
        $clock = static function () use (&$clockValues, &$lastClockValue) {
            if (count($clockValues) > 0) {
                $lastClockValue = array_shift($clockValues);
            }
            return $lastClockValue;
        };
        $api = new NextCloudImageUploadFakeApi();
        $service = $this->createService($api, [
            'clock_milliseconds' => $clock,
            'deadline_seconds' => 25,
        ]);

        $this->assertUploadException(504, 'UPLOAD_TIMEOUT', function () use ($service, $jpegPath) {
            $service->uploadFiles($this->createFilesField([
                ['path' => $jpegPath, 'name' => 'image.jpg'],
            ]));
        });
        self::assertSame([], $api->calls);
    }

    public function testApiTransportDefaultsRemainBackwardCompatibleAndCanBeHardened(): void
    {
        $hadOriginalConfig = array_key_exists('sugar_config', $GLOBALS);
        $originalConfig = $GLOBALS['sugar_config'] ?? null;
        $GLOBALS['sugar_config'] = is_array($originalConfig) ? $originalConfig : [];
        $GLOBALS['sugar_config']['next-cloud'] = [
            'user' => 'test-user',
            'password' => 'test-password',
            'endpoint' => 'https://nextcloud.example.com/remote.php/dav/files',
            'base_url_ocs' => 'https://nextcloud.example.com/ocs/v2.php/apps/files_sharing/api/v1/shares',
        ];

        try {
            $defaultApi = new APINextCloud();
            self::assertFalse($this->readPrivateProperty($defaultApi, 'VERIFY_SSL'));
            self::assertTrue($this->readPrivateProperty($defaultApi, 'FOLLOW_REDIRECTS'));
            self::assertSame(20000, $this->readPrivateProperty($defaultApi, 'CONNECT_TIMEOUT_MS'));
            self::assertSame(60000, $this->readPrivateProperty($defaultApi, 'TIMEOUT_MS'));

            $hardenedApi = new APINextCloud([
                'verify_ssl' => true,
                'follow_redirects' => false,
                'connect_timeout_ms' => 5000,
                'timeout_ms' => 22000,
                'deadline_at_ms' => 123456789,
            ]);
            self::assertTrue($this->readPrivateProperty($hardenedApi, 'VERIFY_SSL'));
            self::assertFalse($this->readPrivateProperty($hardenedApi, 'FOLLOW_REDIRECTS'));
            self::assertSame(5000, $this->readPrivateProperty($hardenedApi, 'CONNECT_TIMEOUT_MS'));
            self::assertSame(22000, $this->readPrivateProperty($hardenedApi, 'TIMEOUT_MS'));
            self::assertSame(123456789.0, $this->readPrivateProperty($hardenedApi, 'DEADLINE_AT_MS'));
            self::assertSame(
                'https://nextcloud.example.com/remote.php/dav/files/test-user/bmvmb/chat_uploads/image%201.jpg',
                $this->invokePrivateMethod(
                    $hardenedApi,
                    'buildDavUrl',
                    ['/bmvmb/chat_uploads/image 1.jpg']
                )
            );
            self::assertSame(
                'https://nextcloud.example.com/remote.php/dav/files/test-user/bmvmb/chat_uploads/',
                $this->invokePrivateMethod(
                    $hardenedApi,
                    'buildDavUrl',
                    ['bmvmb/chat_uploads', true]
                )
            );
        } finally {
            if ($hadOriginalConfig) {
                $GLOBALS['sugar_config'] = $originalConfig;
            } else {
                unset($GLOBALS['sugar_config']);
            }
        }
    }

    public function testDefaultRemoteIdentifierUsesRandom128BitHex(): void
    {
        $api = new NextCloudImageUploadFakeApi();
        $service = new NextCloudImageUploadService($api, ['cleanup_api' => $api]);
        $provider = $this->readPrivateProperty($service, 'uniqueIdProvider');

        $first = call_user_func($provider);
        $second = call_user_func($provider);

        self::assertMatchesRegularExpression('/^[a-f0-9]{32}$/D', $first);
        self::assertMatchesRegularExpression('/^[a-f0-9]{32}$/D', $second);
        self::assertNotSame($first, $second);
    }

    private function createService(NextCloudImageUploadFakeApi $api, array $options = [])
    {
        $uniqueIdCounter = 0;
        $unixTimeCounter = 0;
        $defaults = [
            'cleanup_api' => $api,
            'uploaded_file_validator' => static function () {
                return true;
            },
            'clock_milliseconds' => static function () {
                return 1000000;
            },
            'date_provider' => static function () {
                return new DateTimeImmutable('2026-07-03 12:00:00', new DateTimeZone('Asia/Ho_Chi_Minh'));
            },
            'unique_id_provider' => static function () use (&$uniqueIdCounter) {
                $uniqueIdCounter++;
                return 'fixture' . $uniqueIdCounter;
            },
            'unix_time_provider' => static function () use (&$unixTimeCounter) {
                return 1783012345 + $unixTimeCounter++;
            },
            'deadline_seconds' => 25,
        ];

        return new NextCloudImageUploadService($api, array_replace($defaults, $options));
    }

    private function createFilesField(array $files)
    {
        $field = [
            'name' => [],
            'tmp_name' => [],
            'error' => [],
            'size' => [],
        ];

        foreach ($files as $file) {
            $field['name'][] = $file['name'];
            $field['tmp_name'][] = $file['path'];
            $field['error'][] = $file['error'] ?? UPLOAD_ERR_OK;
            $field['size'][] = $file['size'] ?? filesize($file['path']);
        }

        return $field;
    }

    private function createImage($format, $width, $height)
    {
        $function = 'image' . $format;
        if (!function_exists('imagecreatetruecolor') || !function_exists($function)) {
            self::markTestSkipped("The current PHP/GD runtime cannot generate {$format} fixtures");
        }

        $image = imagecreatetruecolor($width, $height);
        if ($image === false) {
            self::fail('Unable to allocate an image fixture');
        }

        $color = imagecolorallocate(
            $image,
            25 + ($this->temporaryFileCounter % 200),
            80,
            160
        );
        imagefill($image, 0, 0, $color);
        $path = $this->nextTemporaryPath('image.' . $format);

        if ($format === 'jpeg') {
            $written = imagejpeg($image, $path, 90);
        } elseif ($format === 'png') {
            $written = imagepng($image, $path);
        } elseif ($format === 'webp') {
            $written = imagewebp($image, $path, 90);
        } elseif ($format === 'gif') {
            $written = imagegif($image, $path);
        } else {
            imagedestroy($image);
            self::fail('Unsupported test image format');
        }

        imagedestroy($image);
        self::assertTrue($written, 'Unable to write image fixture');
        clearstatcache(true, $path);

        return $path;
    }

    private function createRawFile($contents)
    {
        $path = $this->nextTemporaryPath('raw.bin');
        self::assertSame(strlen($contents), file_put_contents($path, $contents));
        clearstatcache(true, $path);
        return $path;
    }

    private function nextTemporaryPath($suffix)
    {
        $this->temporaryFileCounter++;
        return $this->temporaryDirectory . '/fixture-' . $this->temporaryFileCounter . '-' . $suffix;
    }

    private function assertUploadException($httpStatus, $errorCode, callable $callback)
    {
        try {
            call_user_func($callback);
            self::fail('Expected NextCloudImageUploadException was not thrown');
        } catch (NextCloudImageUploadException $exception) {
            self::assertSame($httpStatus, $exception->getHttpStatus());
            self::assertSame($errorCode, $exception->getErrorCode());
            return $exception;
        }
    }

    private function firstArguments(array $calls)
    {
        return array_map(static function (array $call) {
            return $call['arguments'][0];
        }, $calls);
    }

    private function findCallIndex(NextCloudImageUploadFakeApi $api, $method, $firstArgument)
    {
        foreach ($api->calls as $index => $call) {
            if ($call['method'] === $method && ($call['arguments'][0] ?? null) === $firstArgument) {
                return $index;
            }
        }

        self::fail("Unable to find {$method} call");
    }

    private function readPrivateProperty($object, $propertyName)
    {
        $property = new ReflectionProperty(get_class($object), $propertyName);
        $property->setAccessible(true);
        return $property->getValue($object);
    }

    private function invokePrivateMethod($object, $methodName, array $arguments = [])
    {
        $method = new ReflectionMethod(get_class($object), $methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $arguments);
    }
}
