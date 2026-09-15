<!-- next: web-1 -->
---
type: host
status: up
---
# Primary Web Node

Primary application server, running in the **eu-central-1** region.

- OS: Ubuntu 26.04 LTS
- vCPUs: 4
- Memory: 8 GiB

<!-- next: db-1 -->
---
type: host
status: up
---
# Primary Database Node

Primary database server, same region as `web-1`.

- OS: Ubuntu 26.04 LTS
- vCPUs: 8
- Memory: 32 GiB

<!-- next: web-service -->
---
type: service
status: up
runs_on: [web-1]
depends_on: [db-service]
---
# Web Frontend

Handles incoming HTTP traffic and renders the storefront pages.

Restarting this service is safe during business hours: it drains
existing connections gracefully within **30 seconds** before shutting down.

<!-- next: db-service -->
---
type: service
status: up
runs_on: [db-1]
---
# Database Layer

Owns the primary datastore for products, orders, and sessions.

Backups run nightly at `02:00 UTC` and are retained for 30 days.

<!-- next: storefront -->
---
type: application
status: up
composed_of: [web-service, db-service]
---
# Storefront Application

The customer-facing storefront application, composed of:

- `web-service`, for the public-facing pages
- `db-service`, for persistence

See the [status page](https://status.example.com) for current uptime.
