self.addEventListener('install', function (event) {
    event.waitUntil(self.skipWaiting());
});

self.addEventListener('activate', function (event) {
    event.waitUntil(self.clients.claim());
});

self.addEventListener('message', (event) => {
    if (event.data.type === 'show_notification') {
        self.registration.showNotification('BM-VMB', {
            body: 'Cuộc gọi đến: ' + event.data.phone,
            tag: 'bm-tcb',
            icon: '', 
            dir: 'ltr',
            image: '', 
            actions: [
                { action: 'accept_call', title: 'Trả lời' },
                { action: 'reject_call', title: 'Từ chối' },
            ],
            vibrate: [300, 100, 300, 100, 300, 100, 300],
            requireInteraction: false,
            renotify: true,
            timestamp: Date.now(),
        });
    } else if(event.data.type === 'close_notification'){
        self.registration.getNotifications({ tag: 'bm-tcb' }).then((notifications) => {
            notifications.forEach((notification) => {
                notification.close();
            });
        });
    }
});

self.addEventListener('notificationclick', event => {
    const action = event.action;

    event.waitUntil(
        self.registration.getNotifications({ tag: 'bm-tcb' })
            .then(notifications => {
                notifications.forEach(notification => notification.close());
            })
            .then(() => {
                if (action === 'accept_call') {
                    return self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(clientList => {
                        for (let i = 0; i < clientList.length; i++) {
                            const client = clientList[i];
                            if (client.url.includes('bm.vemaybay.website')) {
                                client.postMessage({
                                    type: 'ACCEPT_CALL',
                                    id: 'toast-incoming',
                                });
                                return client.focus();
                            }
                        }
                    }).catch(error => {
                        console.error('Error focusing client:', error);
                    });
                } else if (action === 'reject_call') {
                    // Xử lý hành động "Từ chối"
                    return self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(clients => {
                        clients.forEach(client => {
                            client.postMessage({
                                type: 'REJECT_CALL'
                            });
                        });
                    }).catch(error => {
                        console.error('Error posting message:', error);
                    });
                }
            })
            .catch(error => {
                console.error('Error handling notification click:', error);
            })
    );
});
