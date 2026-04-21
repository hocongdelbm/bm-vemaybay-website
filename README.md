## Local Development (Docker)

### Prerequisites
- [Docker](https://docs.docker.com/get-docker/) and [Docker Compose](https://docs.docker.com/compose/install/)
- Access to the project database (ask your team for a dump)

### Steps

**1. Clone the repository**
```bash
git clone git@github.com:qndonline/bm.vemaybay.website.git
cd bm.vemaybay.website
```

**2. Build the image**
```bash
docker compose build
```

**3. Install PHP dependencies**
```bash
docker compose run --rm web composer install
```

**4. Configure the application**

Copy the config override template and fill in your database credentials:
```bash
cp config_override.php.example config_override.php
```
Edit `config_override.php` with your database host, name, user, and password.

**5. Start the server**
```bash
docker compose up -d
```

The application will be available at **http://localhost:8001**

### Daily usage

```bash
# Start
docker compose up -d

# Stop
docker compose down

# Run a one-off command (e.g. composer require)
docker compose exec web composer require vendor/package
```
