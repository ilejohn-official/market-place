# Marketplace API

1) Copy `.env.example` to `.env` and set DB credentials.
2) Run `composer install`.
3) Run `php artisan key:generate`.
4) Run `php artisan migrate --seed`.
5) Start the server with `php artisan serve`.

Core endpoints:
- `POST /api/auth/register`, `POST /api/auth/login`
- `POST /api/services`, `GET /api/services`, `GET /api/services/{id}`
- `POST /api/bookings`, `GET /api/bookings`, `GET /api/bookings/{id}`
- `POST /api/bookings/{id}/escrow`, `PATCH /api/bookings/{id}/mark-complete`, `PATCH /api/bookings/{id}/approve`
- `POST /api/bookings/{id}/disputes`, `GET /api/disputes`, `PATCH /api/disputes/{id}/resolve`
- `POST /api/bookings/{id}/review`, `GET /api/sellers/{id}/reviews`
