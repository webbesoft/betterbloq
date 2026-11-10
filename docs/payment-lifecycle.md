# Payment lifecycle and recovery

Checkout charges original product prices upfront in USD. Each checkout belongs to one vendor and one purchase cycle. Finalization applies each product pool's achieved discount to its original line price, capped at the amount actually charged. Storage fees are not discounted. Storage quotes use the existing seven-day grace period and warehouse space/stacking rules, with daily rates derived from day/week/30-day-month pricing.

## Deployment

1. Back up the database. Pause checkout writes and finalization workers while applying the schema and aggregate repair. Historical migration edits only make foreign-key/index removal portable; already-applied migrations do not rerun.
2. Deploy and run `php artisan migrate --force`. The new migration converts monetary line columns to decimal, adds integer-cent price snapshots and durable operation tables, consolidates duplicate cycle/product aggregates, rebuilds quantities from committed line items, and adds composite uniqueness. Review aggregate counts on a production-sized staging database first. Financial history intentionally has no automatic destructive rollback.
3. Configure `STRIPE_WEBHOOK_SECRET` and enable these Checkout events on the existing Cashier endpoint: `checkout.session.completed`, `checkout.session.async_payment_succeeded`, `checkout.session.async_payment_failed`, and `checkout.session.expired`. Keep Cashier's subscription/invoice event subscriptions. The endpoint fails closed if its signing secret is missing.
4. Restart application and queue workers. Run the scheduler on shared infrastructure. The default database cache provides shared notification/scheduler locks. Configure worker timeout below the queue connection's `retry_after`; the finalization job timeout is 60 seconds and the default retry window is 90 seconds.
5. Run a Stripe test-mode checkout, delayed-payment success/failure, and refund on staging before resuming production checkout. Automated tests mock the Stripe gateway; they do not verify credentials or provider configuration.

## Recovery commands

- `php artisan payments:reconcile-checkouts` recovers session creation responses, creates orders for paid sessions, and expires abandoned attempts using Stripe's actual session status. It runs every five minutes.
- `php artisan payments:finalize-cycle CYCLE_ID` starts or resumes finalization. It refuses unresolved checkouts. Product discounts freeze when finalization starts, and each order gets one durable operation. Cycles with partial failures remain retryable.
- `php artisan payments:resume-finalizations` schedules recovery for expired worker leases and incomplete persisted operations. It runs every five minutes.
- `php artisan payments:send-notifications` sends the committed notification outbox. It runs every minute. Mail failures cannot undo payments. Delivery is at least once: a process crash after the mail provider accepts a message but before the sent marker is saved can duplicate an email.

Session creation retries reuse the original payload and key. Identical submissions reuse an existing checkout, including a recently completed checkout until its original expiration. An identical new purchase can start after that period. Monetary operations save their exact request before contacting Stripe. Known pending refunds are polled by ID. After 23 hours, an uncertain operation is looked up instead of submitting a potentially expired idempotency key. Missing or contradictory provider results require operator reconciliation; they never trigger a blind replacement refund.

## Historical orders

Old orders lack reliable original-price snapshots, and some may already have received checkout discounts or external refunds. Do not infer their original prices from today's catalog. Finalization refuses missing snapshots and pre-existing unrelated refunds.

After checking the original checkout/invoice and verifying each line, record original unit prices in USD cents:

```sh
php artisan payments:reconcile-order-prices ORDER_ID '{"LINE_ID":10000,"OTHER_PRODUCT_LINE_ID":2500}'
```

Use actual numeric line IDs as the JSON keys. Include every product line and omit storage lines. This command checks the captured Stripe amount, currency, existing refunds, and local line totals; it refuses to modify an existing payment operation and does not move money. Then resume the cycle. Inconsistent legacy vendor/cycle links, missing storage associations, or payments with unrelated refunds need an explicit accounting reconciliation before proceeding.

## Validation limits

The regression suite covers signed webhook handling, payment failures and retries, amount validation, checkout reuse, per-line discounts, storage fees, notification isolation, and lease recovery using SQLite and a mocked Stripe gateway. Database row-lock behavior and DDL must also be validated on the deployment's MariaDB/MySQL version; SQLite cannot establish production concurrency behavior.
