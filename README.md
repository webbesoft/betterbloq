# Betterbloq

## Deploying with kamal

# Deploy to staging (reads from .env.staging)

```bash
make kamal-deploy DEST=staging
```

# or simply

```bash
make kamal-deploy -d staging
```

# Deploy to production (reads from .env.production)

```bash
make kamal-deploy DEST=production
```

# Setup staging environment

```bash
make kamal-setup DEST=staging
```

# Deploy just the app

```bash
make kamal-app DEST=staging
```

## Testing Discipline

| What           | AI CAN Do               | AI MUST NOT Do           |
| -------------- | ----------------------- | ------------------------ |
| Implementation | Generate business logic | Touch test files         |
| Test Planning  | Suggest test scenarios  | Write test code          |
| Debugging      | Analyze test failures   | Modify test expectations |

If an AI tool touches a test file, the PR gets rejected. No exceptions.
