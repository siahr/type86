DROP TABLE IF EXISTS sandbox;
CREATE TABLE sandbox (
  id				 serial NOT NULL,
  f1         text,
  f2         text,
  f3         text,
  created    timestamp,
  modified   timestamp,
  CONSTRAINT sandbox_pkey PRIMARY KEY (id)
);
