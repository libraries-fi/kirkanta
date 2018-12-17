
CREATE TYPE langcode AS enum('en', 'fi', 'ru', 'se', 'sv');

ALTER TABLE cities ADD COLUMN default_langcode langcode NOT NULL DEFAULT 'fi';
ALTER TABLE regions ADD COLUMN default_langcode langcode NOT NULL DEFAULT 'fi';
ALTER TABLE provincial_libraries ADD COLUMN default_langcode langcode NOT NULL DEFAULT 'fi';
ALTER TABLE consortiums ADD COLUMN default_langcode langcode NOT NULL DEFAULT 'fi';

ALTER TABLE organisations ADD COLUMN default_langcode langcode NOT NULL DEFAULT 'fi';
ALTER TABLE persons ADD COLUMN default_langcode langcode NOT NULL DEFAULT 'fi';
ALTER TABLE services ADD COLUMN default_langcode langcode NOT NULL DEFAULT 'fi';
ALTER TABLE service_instances ADD COLUMN default_langcode langcode NOT NULL DEFAULT 'fi';
ALTER TABLE periods ADD COLUMN default_langcode langcode NOT NULL DEFAULT 'fi';
ALTER TABLE finna_additions ADD COLUMN default_langcode langcode NOT NULL DEFAULT 'fi';
ALTER TABLE pictures ADD COLUMN default_langcode langcode NOT NULL DEFAULT 'fi';
