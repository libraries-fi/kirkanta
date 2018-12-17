
ALTER TABLE cities DROP COLUMN default_langcode;
ALTER TABLE regions DROP COLUMN default_langcode;
ALTER TABLE provincial_libraries DROP COLUMN default_langcode;
ALTER TABLE consortiums DROP COLUMN default_langcode;

ALTER TABLE organisations DROP COLUMN default_langcode;
ALTER TABLE persons DROP COLUMN default_langcode;
ALTER TABLE services DROP COLUMN default_langcode;
ALTER TABLE service_instances DROP COLUMN default_langcode;
ALTER TABLE periods DROP COLUMN default_langcode;
ALTER TABLE finna_additions DROP COLUMN default_langcode;
ALTER TABLE pictures DROP COLUMN default_langcode;

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



------


ALTER TABLE cities_data RENAME COLUMN langcode TO langcode_old;
ALTER TABLE regions_data RENAME COLUMN langcode TO langcode_old;
ALTER TABLE provincial_libraries_data RENAME COLUMN langcode TO langcode_old;
ALTER TABLE consortiums_data RENAME COLUMN langcode TO langcode_old;

ALTER TABLE organisations_data RENAME COLUMN langcode TO langcode_old;
ALTER TABLE persons_data RENAME COLUMN langcode TO langcode_old;
ALTER TABLE services_data RENAME COLUMN langcode TO langcode_old;
ALTER TABLE service_instances_data RENAME COLUMN langcode TO langcode_old;
ALTER TABLE periods_data RENAME COLUMN langcode TO langcode_old;
ALTER TABLE finna_additions_data RENAME COLUMN langcode TO langcode_old;
ALTER TABLE pictures_data RENAME COLUMN langcode TO langcode_old;



ALTER TABLE cities_data ADD COLUMN langcode langcode NOT NULL DEFAULT 'fi';
ALTER TABLE regions_data ADD COLUMN langcode langcode NOT NULL DEFAULT 'fi';
ALTER TABLE provincial_libraries_data ADD COLUMN langcode langcode NOT NULL DEFAULT 'fi';
ALTER TABLE consortiums_data ADD COLUMN langcode langcode NOT NULL DEFAULT 'fi';

ALTER TABLE organisations_data ADD COLUMN langcode langcode NOT NULL DEFAULT 'fi';
ALTER TABLE persons_data ADD COLUMN langcode langcode NOT NULL DEFAULT 'fi';
ALTER TABLE services_data ADD COLUMN langcode langcode NOT NULL DEFAULT 'fi';
ALTER TABLE service_instances_data ADD COLUMN langcode langcode NOT NULL DEFAULT 'fi';
ALTER TABLE periods_data ADD COLUMN langcode langcode NOT NULL DEFAULT 'fi';
ALTER TABLE finna_additions_data ADD COLUMN langcode langcode NOT NULL DEFAULT 'fi';
ALTER TABLE pictures_data ADD COLUMN langcode langcode NOT NULL DEFAULT 'fi';



ALTER TABLE cities_data ADD COLUMN langcode langcode NOT NULL DEFAULT 'fi';
ALTER TABLE regions_data ADD COLUMN langcode langcode NOT NULL DEFAULT 'fi';
ALTER TABLE provincial_libraries_data ADD COLUMN langcode langcode NOT NULL DEFAULT 'fi';
ALTER TABLE consortiums_data ADD COLUMN langcode langcode NOT NULL DEFAULT 'fi';

ALTER TABLE organisations_data ADD COLUMN langcode langcode NOT NULL DEFAULT 'fi';
ALTER TABLE persons_data ADD COLUMN langcode langcode NOT NULL DEFAULT 'fi';
ALTER TABLE services_data ADD COLUMN langcode langcode NOT NULL DEFAULT 'fi';
ALTER TABLE service_instances_data ADD COLUMN langcode langcode NOT NULL DEFAULT 'fi';
ALTER TABLE periods_data ADD COLUMN langcode langcode NOT NULL DEFAULT 'fi';
ALTER TABLE finna_additions_data ADD COLUMN langcode langcode NOT NULL DEFAULT 'fi';
ALTER TABLE pictures_data ADD COLUMN langcode langcode NOT NULL DEFAULT 'fi';







UPDATE cities_data SET langcode = langcode_old::langcode;
UPDATE regions_data SET langcode = langcode_old::langcode;
UPDATE provincial_libraries_data SET langcode = langcode_old::langcode;
UPDATE consortiums_data SET langcode = langcode_old::langcode;

UPDATE organisations_data SET langcode = langcode_old::langcode;
UPDATE persons_data SET langcode = langcode_old::langcode;
UPDATE services_data SET langcode = langcode_old::langcode;
UPDATE service_instances_data SET langcode = langcode_old::langcode;
UPDATE periods_data SET langcode = langcode_old::langcode;
UPDATE finna_additions_data SET langcode = langcode_old::langcode;
UPDATE pictures_data SET langcode = langcode_old::langcode;






ALTER TABLE cities_data DROP CONSTRAINT cities_data_pkey;  ALTER TABLE cities_data ADD PRIMARY KEY (entity_id, langcode);
ALTER TABLE regions_data DROP CONSTRAINT regions_data_pkey;  ALTER TABLE regions_data ADD PRIMARY KEY (entity_id, langcode);
ALTER TABLE provincial_libraries_data DROP CONSTRAINT provincial_libraries_data_pkey;  ALTER TABLE provincial_libraries_data ADD PRIMARY KEY (entity_id, langcode);
ALTER TABLE consortiums_data DROP CONSTRAINT consortiums_data_pkey;  ALTER TABLE consortiums_data ADD PRIMARY KEY (entity_id, langcode);

ALTER TABLE organisations_data DROP CONSTRAINT organisations_data_pkey;  ALTER TABLE organisations_data ADD PRIMARY KEY (entity_id, langcode);
ALTER TABLE persons_data DROP CONSTRAINT persons_data_pkey;  ALTER TABLE persons_data ADD PRIMARY KEY (entity_id, langcode);
ALTER TABLE services_data DROP CONSTRAINT services_data_pkey;  ALTER TABLE services_data ADD PRIMARY KEY (entity_id, langcode);
ALTER TABLE service_instances_data DROP CONSTRAINT service_instances_data_pkey;  ALTER TABLE service_instances_data ADD PRIMARY KEY (entity_id, langcode);
ALTER TABLE periods_data DROP CONSTRAINT periods_data_pkey;  ALTER TABLE periods_data ADD PRIMARY KEY (entity_id, langcode);
ALTER TABLE finna_additions_data DROP CONSTRAINT finna_additions_data_pkey;  ALTER TABLE finna_additions_data ADD PRIMARY KEY (entity_id, langcode);

ALTER TABLE pictures_data DROP CONSTRAINT photos_data_pkey;  ALTER TABLE pictures_data ADD PRIMARY KEY (entity_id, langcode);





ALTER TABLE addresses ADD COLUMN default_langcode langcode NOT NULL DEFAULT 'fi';
ALTER TABLE addresses_data RENAME COLUMN langcode TO langcode_old;
ALTER TABLE addresses_data ADD COLUMN langcode langcode NOT NULL DEFAULT 'fi';
UPDATE addresses_data SET langcode = langcode_old::langcode;
ALTER TABLE addresses_data DROP CONSTRAINT addresses_data_pkey;
ALTER TABLE addresses_data ADD PRIMARY KEY (entity_id, langcode);








ALTER TABLE contact_info ADD COLUMN default_langcode langcode NOT NULL DEFAULT 'fi';
ALTER TABLE contact_info_data RENAME COLUMN langcode TO langcode_old;
ALTER TABLE contact_info_data ADD COLUMN langcode langcode NOT NULL DEFAULT 'fi';
UPDATE contact_info_data SET langcode = langcode_old::langcode;
ALTER TABLE contact_info_data DROP CONSTRAINT contact_info_data_pkey;
ALTER TABLE contact_info_data ADD PRIMARY KEY (entity_id, langcode);






ALTER TABLE cities_data DROP COLUMN langcode_old;
ALTER TABLE regions_data DROP COLUMN langcode_old;
ALTER TABLE provincial_libraries_data DROP COLUMN langcode_old;
ALTER TABLE consortiums_data DROP COLUMN langcode_old;

ALTER TABLE organisations_data DROP COLUMN langcode_old;
ALTER TABLE persons_data DROP COLUMN langcode_old;
ALTER TABLE services_data DROP COLUMN langcode_old;
ALTER TABLE service_instances_data DROP COLUMN langcode_old;
ALTER TABLE periods_data DROP COLUMN langcode_old;
ALTER TABLE finna_additions_data DROP COLUMN langcode_old;
ALTER TABLE pictures_data DROP COLUMN langcode_old;
ALTER TABLE addresses_data DROP COLUMN langcode_old;








ALTER TABLE departments ADD COLUMN default_langcode langcode NOT NULL DEFAULT 'fi';
ALTER TABLE departments_data RENAME COLUMN langcode TO langcode_old;
ALTER TABLE departments_data ADD COLUMN langcode langcode NOT NULL DEFAULT 'fi';
UPDATE departments_data SET langcode = langcode_old::langcode;
ALTER TABLE departments_data DROP CONSTRAINT departments_data_pkey;
ALTER TABLE departments_data ADD PRIMARY KEY (entity_id, langcode);
ALTER TABLE departments_data DROP COLUMN langcode_old;

ALTER TABLE contact_info_data DROP COLUMN langcode_old;
