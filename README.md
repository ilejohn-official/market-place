# Marketplace API

1) Copy `.env.example` to `.env` and set DB credentials.
2) Run `composer install`.
3) Run `php artisan key:generate`.
4) Run `php artisan migrate --seed`.
5) Start the server with `php artisan serve`.

Architecture choices (simple):
- Monolith Laravel API with services for business logic.
- DB transactions for escrow, payout, and dispute resolution.
- Events/listeners are stubs to show notification points.
- Wallet + escrow are mocked to keep payment flow simple.

Ignored (and why):
- Real-time infra, queues, and external payment callbacks to keep scope small.
- Full admin dashboards and analytics because not core to the flow.
- Heavy search/filters because discovery only needs a list for the test.

Alternative: a microservices setup with separate payments/notifications services was possible. We used a single Laravel API to keep delivery fast and show the core booking-to-escrow flow clearly.

Core endpoints:
- `POST /api/auth/register`, `POST /api/auth/login`
- `GET /api/sellers`, `GET /api/services`, `GET /api/services/{id}`
- `POST /api/bookings`, `GET /api/bookings`, `GET /api/bookings/{id}`
- `POST /api/bookings/{id}/escrow`, `PATCH /api/bookings/{id}/mark-complete`, `PATCH /api/bookings/{id}/approve`
- `POST /api/bookings/{id}/disputes`, `GET /api/disputes`, `PATCH /api/disputes/{id}/resolve`
- `POST /api/bookings/{id}/review`, `GET /api/sellers/{id}/reviews`
