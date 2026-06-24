# Live Chat — Save Message API

Persist a live chat message into the **EC_Live_Chat_Messages** module.

> **Architecture note**
> This is an HTTP endpoint, not a WebSocket endpoint. Your WebSocket
> server (the Node/PHP process that terminates the browser socket) is the
> one that calls this API. The flow is:
>
> ```
> Browser  ──WS──►  Chat WS server  ──HTTPS POST──►  CRM (this endpoint)
> ```
>
> The browser never calls the CRM directly. When a message arrives over the
> socket, the WS server makes a server-to-server POST to persist it. This is
> why authentication relies on a shared API key + IP allow-list instead of a
> user login.

---

## Endpoint

```
POST https://bm.vemaybay.website/index.php?entryPoint=entryPointGeneralNA
```

| | |
|---|---|
| Method | `POST` (GET also works, but use POST) |
| Auth | No CRM user login. Shared key + IP allow-list (see below). |
| Dispatcher | `custom/entrypoints/entryGeneralNonAuth.php` |
| Handler | `entryLiveChatMessageClass::saveMessage()` |

### Required headers

| Header | Value |
|---|---|
| `Content-Type` | `application/json` |
| `Api-Key` | The shared non-auth key (`sugar_config['api_key']['non_auth_entrypoint']`). Ask the CRM admin. |

### IP allow-list

The dispatcher rejects any request whose source IP is not in the CRM's
`ip_whitelist`. **The public IP of your WebSocket server must be added to
the allow-list** by the CRM admin before calls will succeed. A blocked IP
returns `403`.

---

## Request body

Send `class`, `method`, and a `params` object:

```json
{
  "class": "entryLiveChatMessageClass",
  "method": "saveMessage",
  "params": {
    "src": 1,
    "message": "Hi, is flight VN123 still available?",
    "client_phone": "0901234567",
    "client_url": "https://vemaybay.website/flights/VN123",
    "client_ip": "203.0.113.45",
    "client_user_agent": "Mozilla/5.0 ...",
    "sender_type": "manual",
    "message_type": "text"
  }
}
```

### Parameters

| Field | Type | Required | Default | Notes |
|---|---|---|---|---|
| `src` | int | no | `2` | Message direction. `0` = BM → client, `1` = client → BM. Any other value is stored as `2` (unknown). |
| `message` | string | yes\* | — | Message body. Stored in the record's `description`. \*Required unless `attachment_url` is provided. |
| `attachment_url` | string | yes\* | `""` | URL of an attachment. \*Either `message` or `attachment_url` must be present. Max 150 chars. |
| `attachment_name` | string | no | `""` | Display name of the attachment. Max 80 chars. |
| `message_type` | string | no | `text` | One of `text`, `image`, `file`, `link`. Max 16 chars. |
| `sender_type` | string | no | `manual` | One of `manual`, `bot`, `auto`. Max 16 chars. |
| `title` | string | no | `""` | Stored in the record's `name`. |
| `client_phone` | string | no | `""` | Visitor phone. Max 14 chars. Indexed. |
| `client_url` | string | no | `""` | URL the visitor was viewing. Max 200 chars. |
| `client_ip` | string | no | `""` | Visitor IP (IPv4/IPv6). Pass the **browser's** IP, not the WS server IP. Max 45 chars. |
| `client_user_agent` | string | no | `""` | Visitor user agent. Truncated to 255 chars. |
| `contact_id` | string | no | `""` | Related `Contacts` record id, if known. |
| `booking_id` | string | no | `""` | Related `EC_Flight_Bookings` record id, if known. |

> `client_ip` / `client_user_agent` are **not** auto-detected — the dispatcher
> sees your WS server, not the browser. Capture them from the original
> WebSocket connection and pass them through explicitly.

---

## Response

All responses are JSON with HTTP `200` when they reach the handler.

### Success

```json
{
  "status": 1,
  "message": "Message saved successfully",
  "data": { "id": "8f2c1d4a-9b3e-4e7a-8c21-7d6f5a0b1234" }
}
```

`data.id` is the new record id (GUID).

### Validation error (empty message and attachment)

```json
{
  "status": 0,
  "message": "Invalid message content",
  "data": null
}
```

### Save exception

```json
{
  "status": 0,
  "message": "Exception error",
  "error": "<details> on line <n> in <file>",
  "data": null
}
```

### Dispatcher-level errors (request never reaches the handler)

| HTTP | Body `message` | Cause |
|---|---|---|
| `403` | `Access denied` | Source IP not in `ip_whitelist`. |
| `401` | `Unauthorized` | Missing/wrong `Api-Key`. |
| `400` | `Invalid class name ...` | Wrong `class`. |
| `400` | `Invalid action ...` | Wrong `method`. |
| `405` | `Method not allowed` | Not a POST/GET. |

Treat **success only when `status === 1`** — a `200` with `status: 0` is still a failure.

---

## Examples

### cURL

```bash
curl -X POST 'https://bm.vemaybay.website/index.php?entryPoint=entryPointGeneralNA' \
  -H 'Content-Type: application/json' \
  -H 'Api-Key: YOUR_NON_AUTH_API_KEY' \
  -d '{
    "class": "entryLiveChatMessageClass",
    "method": "saveMessage",
    "params": {
      "src": 1,
      "message": "Hi, is flight VN123 still available?",
      "client_phone": "0901234567",
      "client_url": "https://vemaybay.website/flights/VN123",
      "client_ip": "203.0.113.45",
      "message_type": "text"
    }
  }'
```

### Node.js WebSocket server (the typical caller)

```js
const CRM_URL  = 'https://bm.vemaybay.website/index.php?entryPoint=entryPointGeneralNA';
const API_KEY  = process.env.CRM_NON_AUTH_API_KEY;

async function saveLiveChatMessage(params) {
  const res = await fetch(CRM_URL, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Api-Key': API_KEY,
    },
    body: JSON.stringify({
      class: 'entryLiveChatMessageClass',
      method: 'saveMessage',
      params,
    }),
  });

  const data = await res.json();
  if (data.status !== 1) {
    throw new Error(`CRM save failed: ${data.message}`);
  }
  return data.data.id; // CRM record id
}

// Inside your WS connection handler:
wss.on('connection', (socket, req) => {
  const clientIp = req.headers['x-forwarded-for'] || req.socket.remoteAddress;
  const clientUA = req.headers['user-agent'];

  socket.on('message', async (raw) => {
    const msg = JSON.parse(raw);          // your own socket protocol
    try {
      const id = await saveLiveChatMessage({
        src: 1,                            // client → BM
        message: msg.text,
        client_phone: msg.phone,
        client_url: msg.url,
        client_ip: clientIp,               // the BROWSER's IP, captured here
        client_user_agent: clientUA,
        sender_type: 'manual',
        message_type: msg.attachmentUrl ? 'file' : 'text',
        attachment_url: msg.attachmentUrl,
        attachment_name: msg.attachmentName,
      });
      socket.send(JSON.stringify({ ok: true, id }));
    } catch (e) {
      socket.send(JSON.stringify({ ok: false, error: e.message }));
    }
  });
});
```

When an **agent replies** from the CRM side, persist it the same way with
`src: 0` (BM → client) before/after pushing it back down the socket.

---

## Quick reference

- **URL:** `POST /index.php?entryPoint=entryPointGeneralNA`
- **Headers:** `Content-Type: application/json`, `Api-Key: <key>`
- **Body:** `{ "class": "entryLiveChatMessageClass", "method": "saveMessage", "params": { ... } }`
- **Success check:** `status === 1`, new id in `data.id`
- **Prereqs:** WS server IP allow-listed + valid `Api-Key`
