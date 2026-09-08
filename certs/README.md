Aiven enforces TLS on its managed MySQL. To connect from this app:

1. In the Aiven console, open your MySQL service → Overview tab.
2. Download the "CA Certificate" and save it here as `aiven-ca.pem`
   (i.e. `certs/aiven-ca.pem`).
3. Commit it (Aiven's CA cert is not a secret — it's a public certificate
   authority cert, not credentials) so it's included when this repo is
   built/deployed on Render.
4. Set `MYSQL_ATTR_SSL_CA=/var/www/certs/aiven-ca.pem` in Render's
   environment variables (already in `.env.example`).

Without this, PHP will attempt a plain (non-SSL) connection, which Aiven
will reject — this is a common cause of "could not connect" or empty-data
errors when this backend is pointed at Aiven MySQL.
