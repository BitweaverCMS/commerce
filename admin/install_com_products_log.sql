-- Product log. Idempotent. Apply once on an existing database.
BEGIN;

CREATE TABLE IF NOT EXISTS com_products_log (
  products_log_id SERIAL PRIMARY KEY,
  products_id integer NOT NULL,
  user_id integer,
  date_added timestamp,
  owner_visible smallint NOT NULL DEFAULT 0,
  log_code varchar(32),
  comments text,
  format_guid varchar(16)
);

CREATE INDEX IF NOT EXISTS products_log_prod_idx ON com_products_log (products_id);

DO $$
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.table_constraints
    WHERE table_name = 'com_products_log' AND constraint_name = 'products_log_prod_ref'
  ) THEN
    ALTER TABLE com_products_log
      ADD CONSTRAINT products_log_prod_ref
      FOREIGN KEY (products_id) REFERENCES com_products (products_id);
  END IF;
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.table_constraints
    WHERE table_name = 'com_products_log' AND constraint_name = 'products_log_user_ref'
  ) THEN
    ALTER TABLE com_products_log
      ADD CONSTRAINT products_log_user_ref
      FOREIGN KEY (user_id) REFERENCES users_users (user_id);
  END IF;
END $$;

COMMIT;
