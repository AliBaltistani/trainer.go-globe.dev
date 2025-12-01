# Push Notification Module API Documentation

This module handles device token management and sending push notifications via Firebase Cloud Messaging (FCM).

## Base URL
`/api`

## Authentication
All endpoints require a valid Bearer Token (Sanctum).
`Authorization: Bearer <token>`

---

## 1. Save Device Token
Save or update the device token for the authenticated user.

**Endpoint:** `POST /save-device-token`

**Headers:**
- `Content-Type: application/json`
- `Authorization: Bearer <token>`

**Body:**
```json
{
  "device_token": "fcm_token_string_here",
  "platform": "android" // Options: android, ios, web
}
```

**Response (200 OK):**
```json
{
  "message": "Device token saved successfully",
  "data": {
    "id": 1,
    "user_id": 5,
    "device_token": "fcm_token_string_here",
    "platform": "android",
    "created_at": "...",
    "updated_at": "..."
  }
}
```

---

## 2. Remove Device Token
Remove a specific device token (e.g., on logout).

**Endpoint:** `POST /remove-device-token`

**Body:**
```json
{
  "device_token": "fcm_token_string_here"
}
```

**Response (200 OK):**
```json
{
  "message": "Device token removed successfully"
}
```

---

## 3. Send Notification to User
Send a push notification to a specific user.

**Endpoint:** `POST /notification/send-to-user`

**Body:**
```json
{
  "user_id": 5,
  "title": "New Booking",
  "message": "You have a new session booking.",
  "payload": {
    "type": "booking",
    "id": 123
  }
}
```

**Response (200 OK):**
```json
{
  "message": "Notification queued successfully",
  "log_id": 10
}
```

---

## 4. Broadcast Notification
Send a broadcast notification to a group of users (Trainer, Client, or All).
This uses FCM Topics (`trainers`, `clients`).

**Endpoint:** `POST /notification/broadcast`

**Body:**
```json
{
  "target": "trainer", // Options: trainer, client, all
  "title": "System Update",
  "message": "The app will be down for maintenance.",
  "payload": {
    "maintenance": true
  }
}
```

**Response (200 OK):**
```json
{
  "message": "Broadcast queued successfully"
}
```

---

## 5. Get Notification History
Retrieve the notification history for the authenticated user.

**Endpoint:** `GET /notifications/history`

**Response (200 OK):**
```json
{
  "current_page": 1,
  "data": [
    {
      "id": 10,
      "user_id": 5,
      "title": "New Booking",
      "message": "You have a new session booking.",
      "payload": {
        "type": "booking",
        "id": 123
      },
      "status": "sent",
      "created_at": "..."
    }
  ],
  "total": 50
  // ... pagination meta
}
```

---

## Internal Services

### FcmService
Located at `App\Services\FcmService`.
Handles direct communication with FCM.

### NotificationService
Located at `App\Services\NotificationService`.
Contains business logic triggers:
- `notifySubscription(User $trainer, User $client)`
- `notifyWorkoutSchedule(User $client, $workoutDetails)`
- `notifyPaymentStatus(User $user, $status, $transactionId)`
- `notifyNewSession(User $user, $sessionDetails)`
- `notifyMessage(User $receiver, User $sender, $message)`
