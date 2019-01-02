ALTER TABLE users ADD COLUMN roles jsonb NOT NULL DEFAULT '["ROLE_USER"]';
ALTER TABLE users RENAME COLUMN role_id TO group_id;

ALTER TABLE services_new RENAME TO service_instances;
ALTER SEQUENCE services_new_id_seq RENAME TO service_instances_id_seq;

ALTER TABLE services RENAME TO services_old;
ALTER TABLE service_types RENAME TO services;
DROP TABLE services_old;

ALTER TABLE organisations DROP COLUMN web_library;
ALTER TABLE cities RENAME COLUMN provincial_library_id TO regional_library_id;

UPDATE organisations SET type = 'other' WHERE type IS NULL;
UPDATE consortiums SET group_id = 2 WHERE group_id IS NULL;



-- As of now we want to ALWAYS store consortium ID in organisations.consortium_id.
UPDATE organisations a SET consortium_id = c.id FROM cities b INNER JOIN consortiums c ON b.consortium_id = c.id WHERE a.force_no_consortium = false AND a.city_id = b.id AND a.consortium_id IS NULL;




-- Extensions are enable per-database.
CREATE EXTENSION postgis;

-- Column for storing coordinates (code 4326 means using the WGS84 coordinate system)
ALTER TABLE addresses ADD COLUMN coordinates geography(POINT, 4326);
CREATE INDEX idx_coordinates ON addresses USING GIST(coordinates);

-- Convert old coordinates to new schema.
UPDATE addresses a SET coordinates = ST_GeographyFromText('POINT(' || REPLACE(b.coordinates, ',', ' ') || ')') FROM organisations b WHERE a.id = b.address_id AND b.coordinates IS NOT NULL;
ALTER TABLE organisations DROP COLUMN coordinates;


-- Alter schema because the single 'Address' entity is split into 'Address' and MailAddress.
ALTER TABLE addresses ADD COLUMN type varchar(40);
UPDATE addresses a SET type = 'address' FROM organisations b WHERE a.id = b.address_id;
UPDATE addresses a SET type = 'mail_address' FROM organisations b WHERE a.id = b.mail_address_id;

-- There appear to be leftovers from some old times.
DELETE FROM addresses WHERE type IS NULL;





ALTER TABLE finna_consortium_data ADD COLUMN special boolean NOT NULL DEFAULT false;
UPDATE finna_consortium_data a SET special = b.special FROM consortiums b WHERE b.id = a.consortium_id;
ALTER TABLE consortiums DROP COLUMN special;


CREATE TABLE photos (
  id serial NOT NULL,
  filename varchar(500) NOT NULL UNIQUE,
  created timestamp with time zone NOT NULL DEFAULT NOW(),
  sizes text[],
  weight int,

  -- Specifies entity type.
  attached_to varchar(40) NOT NULL,

  -- Type-related associations.
  organisation_id INTEGER,
  service_instance_id INTEGER,

  -- organisation fields.
  name varchar(100),
  author varchar(200),
  description varchar(200),
  year smallint,
  translations jsonb,
  meta jsonb,

  -- Set to true when picture is the 'cover photo' of a collection.
  cover boolean NOT NULL DEFAULT false,

  PRIMARY KEY (id),
  FOREIGN KEY(organisation_id) REFERENCES organisations(id) ON DELETE CASCADE,
  FOREIGN KEY(service_instance_id) REFERENCES service_instances(id) ON DELETE CASCADE
);

UPDATE pictures SET name = '' WHERE LENGTH(name) > 100;

INSERT
  INTO photos (filename, created, name, year, author, description, translations, organisation_id, cover, attached_to)
  SELECT filename, created, name, year, author, description, translations, organisation_id, is_default, 'organisation'
  FROM pictures;

UPDATE photos SET sizes = '{small, medium, large, huge}' WHERE organisation_id IS NOT NULL;








/*
 * NOTE: BEGIN SQL FOR SEPARATING TRANSLATABLE FIELDS FROM MAIN TABLES! >>>>>>>>>>>>>>>>>>>>>>
 */

CREATE OR REPLACE FUNCTION slugify(name text) RETURNS text
  AS $$ SELECT translate(regexp_replace(lower(name), E'\\s+', '-', 'g'), 'öäå', 'oaa') $$
  LANGUAGE SQL
  IMMUTABLE
  RETURNS NULL ON NULL INPUT;


CREATE TABLE regions_data (
  entity_id int NOT NULL,
  langcode varchar(2) NOT NULL,
  name varchar(100) NOT NULL,
  slug varchar(100) NOT NULL,

  PRIMARY KEY(entity_id, langcode),
  UNIQUE(langcode, slug),
  FOREIGN KEY(entity_id) REFERENCES regions(id) ON DELETE CASCADE
);

INSERT INTO regions_data
  SELECT
    id,
    'fi',
    name,
    slugify(name)
  FROM regions;

INSERT INTO regions_data
  SELECT
    id,
    'en',
    translations->'en'->>'name',
    slugify(translations->'en'->>'name')
  FROM regions
  WHERE translations->'en'->>'name' != ''
  ;

INSERT INTO regions_data
  SELECT
    id,
    'sv',
    translations->'sv'->>'name',
    slugify(translations->'sv'->>'name')
  FROM regions
  WHERE translations->'sv'->>'name' != ''
  ;

INSERT INTO regions_data
  SELECT
    id,
    'ru',
    translations->'ru'->>'name',
    slugify(translations->'ru'->>'name')
  FROM regions
  WHERE translations->'ru'->>'name' != ''
  ;

ALTER TABLE regions DROP COLUMN name;
ALTER TABLE regions DROP COLUMN slug;
ALTER TABLE regions DROP COLUMN translations;







CREATE TABLE cities_data (
  entity_id int NOT NULL,
  langcode varchar(2) NOT NULL,
  name varchar(100) NOT NULL,
  slug varchar(100) NOT NULL,

  PRIMARY KEY(entity_id, langcode),
  UNIQUE(langcode, slug),
  FOREIGN KEY(entity_id) REFERENCES cities(id) ON DELETE CASCADE
);

INSERT INTO cities_data
  SELECT
    id,
    'fi',
    name,
    slugify(name)
  FROM cities;

INSERT INTO cities_data
  SELECT
    id,
    'en',
    translations->'en'->>'name',
    slugify(translations->'en'->>'name')
  FROM cities
  WHERE translations->'en'->>'name' != ''
  ;

INSERT INTO cities_data
SELECT
  id,
  'sv',
  translations->'sv'->>'name',
  slugify(translations->'sv'->>'name')
FROM cities
WHERE translations->'sv'->>'name' != ''
;

INSERT INTO cities_data
SELECT
  id,
  'ru',
  translations->'ru'->>'name',
  slugify(translations->'ru'->>'name')
FROM cities
WHERE translations->'ru'->>'name' != ''
;

ALTER TABLE cities DROP COLUMN name;
ALTER TABLE cities DROP COLUMN slug;
ALTER TABLE cities DROP COLUMN translations;






CREATE TABLE provincial_libraries_data (
  entity_id int NOT NULL,
  langcode varchar(2) NOT NULL,
  name varchar(100) NOT NULL,
  province varchar(100) NOT NULL,
  slug varchar(100) NOT NULL,

  PRIMARY KEY(entity_id, langcode),
  UNIQUE(langcode, slug),
  FOREIGN KEY(entity_id) REFERENCES provincial_libraries(id) ON DELETE CASCADE
);

INSERT INTO provincial_libraries_data
  SELECT
    id,
    'fi',
    name,
    province,
    slugify(name)
  FROM provincial_libraries;

INSERT INTO provincial_libraries_data
  SELECT
    id,
    'en',
    translations->'en'->>'name',
    translations->'en'->>'province',
    slugify(translations->'en'->>'name')
  FROM provincial_libraries
  WHERE translations->'en'->>'name' != ''
  ;

INSERT INTO provincial_libraries_data
  SELECT
    id,
    'sv',
    translations->'sv'->>'name',
    translations->'sv'->>'province',
    slugify(translations->'sv'->>'name')
  FROM provincial_libraries
  WHERE translations->'sv'->>'name' != ''
  ;

INSERT INTO provincial_libraries_data
  SELECT
    id,
    'ru',
    translations->'ru'->>'name',
    translations->'ru'->>'province',
    slugify(translations->'ru'->>'name')
  FROM provincial_libraries
  WHERE translations->'ru'->>'name' != ''
  ;

ALTER TABLE provincial_libraries DROP COLUMN name;
ALTER TABLE provincial_libraries DROP COLUMN province;
ALTER TABLE provincial_libraries DROP COLUMN slug;
ALTER TABLE provincial_libraries DROP COLUMN translations;






CREATE TABLE persons_data (
  entity_id int NOT NULL,
  langcode varchar(2) NOT NULL,
  job_title varchar(200),
  responsibility varchar(200),

  PRIMARY KEY(entity_id, langcode),
  FOREIGN KEY(entity_id) REFERENCES persons(id) ON DELETE CASCADE
);

INSERT INTO persons_data
  SELECT
    id,
    'fi',
    job_title,
    responsibility
  FROM persons
  ;

INSERT INTO persons_data
  SELECT
    id,
    'en',
    translations->'en'->>'job_title',
    translations->'en'->>'responsibility'
  FROM persons
  WHERE
    translations->'en'->>'job_title' != '' OR
    translations->'en'->>'responsibility' != ''
  ;

INSERT INTO persons_data
  SELECT
    id,
    'sv',
    translations->'sv'->>'job_title',
    translations->'sv'->>'responsibility'
  FROM persons
  WHERE
    translations->'sv'->>'job_title' != '' OR
    translations->'sv'->>'responsibility' != ''
  ;

INSERT INTO persons_data
  SELECT
    id,
    'ru',
    translations->'ru'->>'job_title',
    translations->'ru'->>'responsibility'
  FROM persons
  WHERE
    translations->'ru'->>'job_title' != '' OR
    translations->'ru'->>'responsibility' != ''
  ;

ALTER TABLE persons DROP COLUMN job_title;
ALTER TABLE persons DROP COLUMN responsibility;









CREATE TABLE periods_data (
  entity_id int NOT NULL,
  langcode varchar(2) NOT NULL,
  name varchar(100) NOT NULL,
  description text,

  PRIMARY KEY(entity_id, langcode),
  FOREIGN KEY(entity_id) REFERENCES periods(id) ON DELETE CASCADE
);

INSERT INTO periods_data
  SELECT
    id,
    'fi',
    name,
    description
  FROM periods
  ;

INSERT INTO periods_data
  SELECT
    id,
    'en',
    coalesce(translations->'en'->>'name', 'Period'),
    translations->'en'->>'description'
  FROM periods
  WHERE
    translations->'en'->>'name' != '' OR
    translations->'en'->>'description' != ''
  ;

INSERT INTO periods_data
  SELECT
    id,
    'sv',
    coalesce(translations->'sv'->>'name', 'Period'),
    translations->'sv'->>'description'
  FROM periods
  WHERE
    translations->'sv'->>'name' != '' OR
    translations->'sv'->>'description' != ''
  ;

INSERT INTO periods_data
  SELECT
    id,
    'ru',
    coalesce(translations->'ru'->>'name', 'Period'),
    translations->'ru'->>'description'
  FROM periods
  WHERE
    translations->'ru'->>'name' != '' OR
    translations->'ru'->>'description' != ''
  ;

ALTER TABLE periods DROP COLUMN name;
ALTER TABLE periods DROP COLUMN description;






CREATE TABLE services_data (
  entity_id int NOT NULL,
  langcode varchar(2) NOT NULL,
  name varchar(100) NOT NULL,
  slug varchar(100) NOT NULL,
  description text,

  PRIMARY KEY(entity_id, langcode),
  UNIQUE(langcode, slug),
  FOREIGN KEY(entity_id) REFERENCES services(id) ON DELETE CASCADE
);

INSERT INTO services_data
  SELECT
    id,
    'fi',
    name,
    slug,
    description
  FROM
    services
  ;

INSERT INTO services_data
  SELECT
    id,
    'en',
    translations->'en'->>'name',
    slugify(translations->'en'->>'name') || '-' || round(random() * 10000),
    translations->'en'->>'description'
  FROM
    services
  WHERE
    translations->'en'->>'name' != ''
  ;

INSERT INTO services_data
  SELECT
    id,
    'sv',
    translations->'sv'->>'name',
    slugify(translations->'sv'->>'name') || '-' || round(random() * 10000),
    translations->'sv'->>'description'
  FROM
    services
  WHERE
    translations->'sv'->>'name' != ''
  ;

INSERT INTO services_data
  SELECT
    id,
    'ru',
    translations->'ru'->>'name',
    slugify(translations->'ru'->>'name') || '-' || round(random() * 10000),
    translations->'ru'->>'description'
  FROM
    services
  WHERE
    translations->'ru'->>'name' != ''
  ;

ALTER TABLE services DROP COLUMN name;
ALTER TABLE services DROP COLUMN description;
ALTER TABLE services DROP COLUMN slug;





CREATE TABLE service_instances_data (
  entity_id int NOT NULL,
  langcode varchar(2) NOT NULL,
  name varchar(100),
  short_description varchar(200),
  description text,
  price varchar(100),
  website varchar(255),

  PRIMARY KEY(entity_id, langcode),
  FOREIGN KEY(entity_id) REFERENCES service_instances(id) ON DELETE CASCADE
);

INSERT INTO service_instances_data
  SELECT
    id,
    'fi',
    substr(name, 0, 100),
    short_description,
    description,
    price,
    website
  FROM
    service_instances
  ;

INSERT INTO service_instances_data
  SELECT
    id,
    'en',
    substr(translations->'en'->>'name', 0, 100),
    translations->'en'->>'short_description',
    translations->'en'->>'description',
    translations->'en'->>'price',
    translations->'en'->>'website'
  FROM
    service_instances
  WHERE
    length(concat(
      translations->'en'->>'name',
      translations->'en'->>'short_description',
      translations->'en'->>'description',
      translations->'en'->>'price',
      translations->'en'->>'website'
    )) > 0
  ;

INSERT INTO service_instances_data
  SELECT
    id,
    'sv',
    substr(translations->'sv'->>'name', 0, 100),
    translations->'sv'->>'short_description',
    translations->'sv'->>'description',
    translations->'sv'->>'price',
    translations->'sv'->>'website'
  FROM
    service_instances
  WHERE
    length(concat(
      translations->'sv'->>'name',
      translations->'sv'->>'short_description',
      translations->'sv'->>'description',
      translations->'sv'->>'price',
      translations->'sv'->>'website'
    )) > 0
  ;

INSERT INTO service_instances_data
  SELECT
    id,
    'ru',
    substr(translations->'ru'->>'name', 0, 100),
    translations->'ru'->>'short_description',
    translations->'ru'->>'description',
    translations->'ru'->>'price',
    translations->'ru'->>'website'
  FROM
    service_instances
  WHERE
    length(concat(
      translations->'ru'->>'name',
      translations->'ru'->>'short_description',
      translations->'ru'->>'description',
      translations->'ru'->>'price',
      translations->'ru'->>'website'
    )) > 0
  ;

ALTER TABLE service_instances DROP COLUMN name;
ALTER TABLE service_instances DROP COLUMN short_description;
ALTER TABLE service_instances DROP COLUMN description;
ALTER TABLE service_instances DROP COLUMN price;
ALTER TABLE service_instances DROP COLUMN website;






CREATE TABLE consortiums_data (
  entity_id int NOT NULL,
  langcode varchar(2) NOT NULL,
  name varchar(100) NOT NULL,
  slug varchar(100) NOT NULL,
  homepage varchar(255),
  description text,

  PRIMARY KEY(entity_id, langcode),
  UNIQUE(langcode, slug),
  FOREIGN KEY(entity_id) REFERENCES consortiums(id) ON DELETE CASCADE
);

INSERT INTO consortiums_data
  SELECT
    id,
    'fi',
    name,
    slug,
    homepage,
    description
  FROM
    consortiums
  ;

INSERT INTO consortiums_data
  SELECT
    id,
    'en',
    translations->'en'->>'name',
    slugify(translations->'en'->>'name'),
    translations->'en'->>'homepage',
    translations->'en'->>'description'
  FROM consortiums
  WHERE translations->'en'->>'name' != ''
  ;

INSERT INTO consortiums_data
  SELECT
    id,
    'sv',
    translations->'sv'->>'name',
    slugify(translations->'sv'->>'name'),
    translations->'sv'->>'homepage',
    translations->'sv'->>'description'
  FROM consortiums
  WHERE translations->'sv'->>'name' != ''
  ;




ALTER TABLE finna_consortium_data RENAME TO finna_additions;

CREATE TABLE finna_additions_data (
  entity_id int NOT NULL,
  langcode varchar(2) NOT NULL,
  usage_info text,
  notification text,

  PRIMARY KEY(entity_id, langcode),
  FOREIGN KEY(entity_id) REFERENCES consortiums(id) ON DELETE CASCADE
);

INSERT INTO finna_additions_data
  SELECT
    consortium_id,
    'fi',
    usage_info,
    notification
  FROM
    finna_additions
  ;

INSERT INTO finna_additions_data
  SELECT
    consortium_id,
    'en',
    translations->'en'->>'usage_info',
    translations->'en'->>'notification'
  FROM
    finna_additions
  WHERE length(concat(
    translations->'en'->>'usage_info',
    translations->'en'->>'notification'
  )) > 0
  ;

INSERT INTO finna_additions_data
  SELECT
    consortium_id,
    'sv',
    translations->'sv'->>'usage_info',
    translations->'sv'->>'notification'
  FROM
    finna_additions
  WHERE length(concat(
    translations->'sv'->>'usage_info',
    translations->'sv'->>'notification'
  )) > 0
  ;

INSERT INTO finna_additions_data
  SELECT
    consortium_id,
    'ru',
    translations->'ru'->>'usage_info',
    translations->'ru'->>'notification'
  FROM
    finna_additions
  WHERE length(concat(
    translations->'ru'->>'usage_info',
    translations->'ru'->>'notification'
  )) > 0
  ;

ALTER TABLE finna_additions ADD COLUMN id int;
UPDATE finna_additions SET id = consortium_id;
ALTER TABLE finna_additions DROP CONSTRAINT finna_consortium_data_pkey;
ALTER TABLE finna_additions ADD PRIMARY KEY(id);
ALTER TABLE finna_additions ADD UNIQUE(consortium_id);







CREATE TABLE organisations_data (
  entity_id int NOT NULL,
  langcode varchar(2) NOT NULL,
  name varchar(100) NOT NULL,
  slug varchar(100) NOT NULL,
  short_name varchar(40),
  slogan varchar(200),
  description text,
  email varchar(255) NOT NULL,
  homepage varchar(255),
  transit_directions text,
  parking_instructions text,
  building_name varchar(100),

  PRIMARY KEY(entity_id, langcode),
  UNIQUE(langcode, slug),
  FOREIGN KEY(entity_id) REFERENCES organisations(id) ON DELETE CASCADE
);

INSERT INTO organisations_data
  SELECT
    id,
    'fi',
    name,
    coalesce(slug, slugify(name) || '-' || id),
    substr(short_name, 1, 40),
    slogan,
    description,
    coalesce(email, ''),
    homepage,
    transit_directions,
    parking_instructions,
    building_name
  FROM
    organisations
  ;


INSERT INTO organisations_data
  SELECT
    id,
    'en',
    translations->'en'->>'name',
    coalesce(nullif(translations->'en'->>'slug', ''), slugify(translations->'en'->>'name') || '-' || id),
    substr(translations->'en'->>'short_name', 1, 40),
    translations->'en'->>'slogan',
    translations->'en'->>'description',
    coalesce(translations->'en'->>'email', ''),
    translations->'en'->>'homepage',
    translations->'en'->>'transit_directions',
    translations->'en'->>'parking_instructions',
    translations->'en'->>'building_name'
  FROM
    organisations
  WHERE
    translations->'en'->>'name' != ''
  ;

INSERT INTO organisations_data
  SELECT
    id,
    'sv',
    translations->'sv'->>'name',
    coalesce(nullif(translations->'sv'->>'slug', ''), slugify(translations->'sv'->>'name') || '-' || id),
    substr(translations->'sv'->>'short_name', 1, 40),
    translations->'sv'->>'slogan',
    translations->'sv'->>'description',
    coalesce(translations->'sv'->>'email', ''),
    translations->'sv'->>'homepage',
    translations->'sv'->>'transit_directions',
    translations->'sv'->>'parking_instructions',
    translations->'sv'->>'building_name'
  FROM
    organisations
  WHERE
    translations->'sv'->>'name' != ''
  ;

INSERT INTO organisations_data
  SELECT
    id,
    'ru',
    translations->'ru'->>'name',
    coalesce(nullif(translations->'ru'->>'slug', ''), slugify(translations->'ru'->>'name') || '-' || id),
    substr(translations->'ru'->>'short_name', 1, 40),
    translations->'ru'->>'slogan',
    translations->'ru'->>'description',
    coalesce(translations->'ru'->>'email', ''),
    translations->'ru'->>'homepage',
    translations->'ru'->>'transit_directions',
    translations->'ru'->>'parking_instructions',
    translations->'ru'->>'building_name'
  FROM
    organisations
  WHERE
    translations->'ru'->>'name' != ''
  ;

INSERT INTO organisations_data
  SELECT
    id,
    'se',
    translations->'se'->>'name',
    coalesce(nullif(translations->'se'->>'slug', ''), slugify(translations->'se'->>'name') || '-' || id),
    substr(translations->'se'->>'short_name', 1, 40),
    translations->'se'->>'slogan',
    translations->'se'->>'description',
    coalesce(translations->'se'->>'email', ''),
    translations->'se'->>'homepage',
    translations->'se'->>'transit_directions',
    translations->'se'->>'parking_instructions',
    translations->'se'->>'building_name'
  FROM
    organisations
  WHERE
    translations->'se'->>'name' != ''
  ;

ALTER TABLE organisations DROP COLUMN name;
ALTER TABLE organisations DROP COLUMN short_name;
ALTER TABLE organisations DROP COLUMN slug;
ALTER TABLE organisations DROP COLUMN slogan;
ALTER TABLE organisations DROP COLUMN description;
ALTER TABLE organisations DROP COLUMN email;
ALTER TABLE organisations DROP COLUMN homepage;
ALTER TABLE organisations DROP COLUMN transit_directions;
ALTER TABLE organisations DROP COLUMN parking_instructions;
ALTER TABLE organisations DROP COLUMN building_name;
ALTER TABLE organisations DROP COLUMN translations;






CREATE TABLE photos_data (
  entity_id int NOT NULL,
  langcode varchar(2) NOT NULL,
  entity_type varchar(40) NOT NULL,
  name varchar(100),
  description text,

  PRIMARY KEY(entity_id, langcode),
  FOREIGN KEY(entity_id) REFERENCES photos(id) ON DELETE CASCADE
);

INSERT INTO photos_data
  SELECT
    id,
    'fi',
    attached_to,
    name,
    description
  FROM photos
  ;


INSERT INTO photos_data
  SELECT
    id,
    'en',
    attached_to,
    translations->'en'->>'name',
    translations->'en'->>'description'
  FROM
    photos
  WHERE
    length(concat(
      translations->'en'->>'name',
      translations->'en'->>'description'
    )) > 0
  ;

INSERT INTO photos_data
  SELECT
    id,
    'sv',
    attached_to,
    translations->'sv'->>'name',
    translations->'sv'->>'description'
  FROM
    photos
  WHERE
    length(concat(
      translations->'sv'->>'name',
      translations->'sv'->>'description'
    )) > 0
  ;

INSERT INTO photos_data
  SELECT
    id,
    'ru',
    attached_to,
    translations->'ru'->>'name',
    translations->'ru'->>'description'
  FROM
    photos
  WHERE
    length(concat(
      translations->'ru'->>'name',
      translations->'ru'->>'description'
    )) > 0
  ;

ALTER TABLE photos DROP COLUMN name;
ALTER TABLE photos DROP COLUMN description;
ALTER TABLE photos DROP COLUMN translations;


DROP TABLE pictures;
ALTER TABLE photos RENAME TO pictures;


CREATE TABLE addresses_data (
  entity_id int NOT NULL,
  langcode varchar(2) NOT NULL,
  street varchar(200),
  area varchar(200),
  info varchar(200),

  PRIMARY KEY (entity_id, langcode),
  FOREIGN KEY (entity_id) REFERENCES addresses(id) ON DELETE CASCADE
);

INSERT INTO addresses_data
  SELECT
    id,
    'fi',
    street,
    area
  FROM
    addresses
  ;

INSERT INTO addresses_data
  SELECT
    id,
    'en',
    translations->'en'->>'street',
    translations->'en'->>'area',
    translations->'en'->>'info'
  FROM addresses
  WHERE length(concat(
      translations->'en'->>'street',
      translations->'en'->>'area',
      translations->'en'->>'info'
    )) > 0;
  ;

INSERT INTO addresses_data
  SELECT
    id,
    'sv',
    translations->'sv'->>'street',
    translations->'sv'->>'area',
    translations->'sv'->>'info'
  FROM addresses
  WHERE length(concat(
      translations->'sv'->>'street',
      translations->'sv'->>'area',
      translations->'sv'->>'info'
    )) > 0;
  ;

INSERT INTO addresses_data
  SELECT
    id,
    'ru',
    translations->'ru'->>'street',
    translations->'ru'->>'area',
    translations->'ru'->>'info'
  FROM addresses
  WHERE length(concat(
      translations->'ru'->>'street',
      translations->'ru'->>'area',
      translations->'ru'->>'info'
    )) > 0;
  ;

INSERT INTO addresses_data
  SELECT
    id,
    'se',
    translations->'se'->>'street',
    translations->'se'->>'area',
    translations->'se'->>'info'
  FROM addresses
  WHERE length(concat(
      translations->'se'->>'street',
      translations->'se'->>'area',
      translations->'se'->>'info'
    )) > 0;
  ;



/*
 * NOTE: END SQL FOR SEPARATING TRANSLATABLE FIELDS FROM MAIN TABLES! <<<<<<<<<<<<<<<<<<<<<<<<
 */





/*
 * Swap the mapping column from finna_additions to consortiums because it is
 * optimal for Doctrine (will not fetch the association every single time
 * when loading consortiums).
 */
ALTER TABLE consortiums ADD COLUMN finna_data_id int;
UPDATE consortiums a SET finna_data_id = b.id FROM finna_additions b WHERE b.id = a.id;
ALTER TABLE consortiums ADD FOREIGN KEY (finna_data_id) REFERENCES finna_additions(id) ON DELETE SET NULL;
ALTER TABLE finna_additions DROP COLUMN consortium_id;







ALTER TABLE notifications RENAME COLUMN title TO subject;


















/*
 * NOTE: String translation tables >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
 */

CREATE TABLE translations (
  locale varchar(5) NOT NULL,
  domain varchar(40) NOT NULL,
  source text NOT NULL,
  message text,

  PRIMARY KEY (locale, domain, source)
);





ALTER TABLE roles RENAME TO user_groups;
ALTER TABLE user_groups ADD COLUMN roles varchar(30)[];
UPDATE user_groups SET roles = '{"ROLE_USER"}';
ALTER TABLE user_groups ALTER COLUMN roles SET NOT NULL;







-- NOTE: Foreign key columns do not have suffix '_id' because contents of this table represent
-- standalone API documents.
CREATE TABLE schedules (
  period int NOT NULL,
  library int NOT NULL,
  department int,
  opens timestamp NOT NULL,
  closes timestamp,
  staff boolean,
  status smallint,
  info jsonb,

  UNIQUE(library, department, opens),
  FOREIGN KEY(library)
    REFERENCES organisations(id)
    ON DELETE CASCADE,
  FOREIGN KEY(period)
    REFERENCES periods(id),
  FOREIGN KEY(department)
    REFERENCES organisations(id)
);

CREATE INDEX idx_schedules_date ON schedules(date(opens));

COMMENT ON COLUMN schedules.department IS 'Cannot be null per Postgresql requirements; use library ID when no department';
COMMENT ON COLUMN schedules.opens IS 'Also used to store the date component of the opening time (even when closed)';
COMMENT ON COLUMN schedules.staff IS 'TRUE if staff present, FALSE if self-service, NULL if library closed';
COMMENT ON COLUMN schedules.status IS 'Real-time status of the associated library/section (closed/open/self-service)';






CREATE TYPE facility_role AS enum('organisation', 'department');
ALTER TABLE organisations ADD COLUMN role facility_role DEFAULT 'organisation';
UPDATE organisations SET role = 'department' WHERE type = 'department';

ALTER TABLE periods ADD COLUMN department_id int;
ALTER TABLE periods ADD FOREIGN KEY (department_id) REFERENCES organisations(id) ON DELETE CASCADE;






ALTER TABLE organisations_data ALTER COLUMN email DROP NOT NULL;




CREATE TYPE contact_info_type AS enum('phone', 'email', 'website');

CREATE TABLE contact_info (
  id serial NOT NULL,
  type contact_info_type NOT NULL,
  contact varchar(255) NOT NULL,
  weight int,
  organisation_id int,
  department_id int,

  PRIMARY KEY(id),
  FOREIGN KEY(organisation_id)
    REFERENCES organisations(id)
    ON DELETE CASCADE,
  FOREIGN KEY(department_id)
    REFERENCES organisations(id)
    ON DELETE CASCADE
);

CREATE TABLE contact_info_data (
  entity_id int NOT NULL,
  langcode varchar(2) NOT NULL,
  name varchar(100) NOT NULL,
  description text,

  PRIMARY KEY(entity_id, langcode),
  FOREIGN KEY(entity_id) REFERENCES contact_info(id) ON DELETE CASCADE
);









INSERT INTO contact_info (type, id, contact, organisation_id)
  SELECT 'phone', id, number, organisation_id
  FROM phone_numbers
  WHERE length(number) < 20 AND length(name) <= 100;

INSERT INTO contact_info_data (langcode, entity_id, name, description)
  SELECT 'fi', id, name, description
  FROM phone_numbers
  WHERE length(number) < 20 AND length(name) <= 100;

INSERT INTO contact_info_data (langcode, entity_id, name, description)
    SELECT 'en', id, translations->'en'->>'name', translations->'en'->>'description'
    FROM phone_numbers
    WHERE length(number) < 20 AND length(name) <= 100 AND
      length(translations->'en'->>'name') <= 100 AND
      (translations->'en'->>'name' != '' OR
      translations->'en'->>'description' != '');

INSERT INTO contact_info_data (langcode, entity_id, name, description)
    SELECT 'sv', id, translations->'sv'->>'name', translations->'sv'->>'description'
    FROM phone_numbers
    WHERE length(number) < 20 AND length(name) <= 100 AND
      length(translations->'sv'->>'name') <= 100 AND
      (translations->'sv'->>'name' != '' OR
      translations->'sv'->>'description' != '');

INSERT INTO contact_info_data (langcode, entity_id, name, description)
    SELECT 'se', id, translations->'se'->>'name', translations->'se'->>'description'
    FROM phone_numbers
    WHERE length(number) < 20 AND length(name) <= 100 AND
      length(translations->'se'->>'name') <= 100 AND
      (translations->'se'->>'name' != '' OR
      translations->'se'->>'description' != '');

INSERT INTO contact_info_data (langcode, entity_id, name, description)
    SELECT 'ru', id, translations->'ru'->>'name', translations->'ru'->>'description'
    FROM phone_numbers
    WHERE length(number) < 20 AND length(name) <= 100 AND
      length(translations->'ru'->>'name') <= 100 AND
      (translations->'ru'->>'name' != '' OR
      translations->'ru'->>'description' != '');






ALTER TABLE periods DROP COLUMN continuous;














-- Move library entities under their new class type Library.
ALTER TYPE facility_role ADD VALUE 'library';
UPDATE organisations SET role = 'library' WHERE type = 'library';


ALTER TABLE periods RENAME COLUMN organisation_id TO library_id;
ALTER TABLE service_instances RENAME COLUMN organisation_id TO library_id;
ALTER TABLE persons RENAME COLUMN organisation_id TO library_id;
ALTER TABLE pictures RENAME COLUMN organisation_id TO library_id;
ALTER TABLE contact_info RENAME COLUMN organisation_id TO library_id;
ALTER TABLE addresses RENAME COLUMN organisation_id TO library_id;




ALTER TABLE finna_additions RENAME COLUMN special TO exclusive;

ALTER TABLE finna_additions ADD COLUMN group_id int;
ALTER TABLE finna_additions ADD FOREIGN KEY(group_id) REFERENCES user_groups(id);
UPDATE finna_additions a SET group_id = b.group_id FROM consortiums b WHERE a.id = b.id;



-- Have forgotten to drop these columns before.
ALTER TABLE consortiums DROP COLUMN name;
ALTER TABLE consortiums DROP COLUMN homepage;
ALTER TABLE consortiums DROP COLUMN description;
ALTER TABLE consortiums DROP COLUMN slug;
ALTER TABLE consortiums DROP COLUMN translations;




-- Drop NOT NULL from password fields to allow 'locked accounts'.
ALTER TABLE users ALTER COLUMN password DROP NOT NULL;







-- Create (new) PTV tables
CREATE TABLE ptv_data (
  entity_type varchar(40) NOT NULL,
  entity_id int NOT NULL,
  ptv_identifier uuid,
  enabled boolean NOT NULL DEFAULT false,
  published boolean NOT NULL DEFAULT false,
  last_export timestamptz,
  log text[],

  PRIMARY KEY(entity_id, entity_type),
  UNIQUE(ptv_identifier)
);





UPDATE organisations a SET state = 0 WHERE address_id IS NULL AND role = 'library';









ALTER TABLE organisations ADD COLUMN cached_document jsonb;
ALTER TABLE consortiums ADD COLUMN cached_document jsonb;
ALTER TABLE services ADD COLUMN cached_document jsonb;
ALTER TABLE finna_additions ADD COLUMN cached_document jsonb;

ALTER TABLE organisations RENAME COLUMN type TO old_doc_type;
ALTER TABLE organisations RENAME COLUMN branch_type TO type;

ALTER TYPE facility_role ADD VALUE 'mobile_stop';
UPDATE organisations SET role = 'mobile_stop' WHERE old_doc_type = 'mobile_stop';


ALTER TYPE facility_role ADD VALUE 'meta';
UPDATE organisations SET role = 'meta' WHERE old_doc_type = 'organisation' AND type = 'centralized_service';


-- NOTE: THINK BEFORE EXECUTING THESE IN PRODUCTION!
UPDATE organisations SET role = 'department' WHERE old_doc_type = 'centralized_service';
UPDATE organisations SET role = 'library' WHERE id = 86476;
UPDATE organisations SET role = 'mobile_stop' WHERE type = 'mobile_stop' AND role = 'organisation';

UPDATE organisations SET role = 'meta' WHERE id IN (84712, 84645, 86513, 86511);




ALTER TYPE facility_role ADD VALUE 'foreign';
UPDATE organisations a SET role = 'foreign' FROM organisations_data b WHERE role = 'organisation' AND (b.name ILIKE '%arkisto%' OR b.name ILIKE '%museo%') AND a.id = b.entity_id AND b.langcode = 'fi';

UPDATE organisations SET role = 'foreign' WHERE id IN (86448, 86505, 86475);






UPDATE organisations a SET parent_id = NULL FROM organisations b WHERE a.parent_id = b.id AND a.role = 'library' AND b.role <> 'organisation';

UPDATE organisations SET type = 'other' WHERE role = 'library' AND type IS NULL;
UPDATE organisations SET type = 'other' WHERE role = 'foreign' AND type IS NULL;






ALTER TABLE contact_info ADD COLUMN attached_to facility_role;
UPDATE contact_info SET attached_to = 'library';
ALTER TABLE contact_info ALTER COLUMN attached_to SET NOT NULL;

ALTER TABLE contact_info RENAME COLUMN library_id TO parent_id;

UPDATE contact_info SET parent_id = department_id WHERE department_id IS NOT NULL;
ALTER TABLE contact_info DROP COLUMN department_id;

CREATE OR REPLACE VIEW contact_info_doctrine AS
  SELECT attached_to || ':' || type AS type, id, contact, weight, parent_id
  FROM contact_info a
;

CREATE OR REPLACE RULE split_type_id AS ON INSERT TO contact_info_doctrine
  DO INSTEAD
    INSERT INTO contact_info (id, attached_to, type, contact, weight, parent_id)
    VALUES (
      NEW.id,
      left(NEW.type, position(':' IN NEW.type) - 1)::facility_role,
      substring(NEW.type FROM position(':' IN NEW.type) + 1)::contact_info_type,
      NEW.contact,
      NEW.weight,
      NEW.parent_id
    )
;



ALTER TABLE periods RENAME COLUMN library_id TO parent_id;
ALTER TABLE pictures RENAME COLUMN library_id TO parent_id;
ALTER TABLE service_instances RENAME COLUMN library_id TO parent_id;





INSERT INTO contact_info (type, id, contact, parent_id, attached_to)
  SELECT 'website', id % 100000 + 200000, url, organisation_id, 'library'
  FROM web_links
  WHERE organisation_id IS NOT NULL
;

INSERT INTO contact_info_data (langcode, entity_id, name, description)
  SELECT 'fi', id % 100000 + 200000, name, description
  FROM web_links
  WHERE organisation_id IS NOT NULL
;

INSERT INTO contact_info_data (langcode, entity_id, name, description)
  SELECT 'en', id % 100000 + 200000, translations->'en'->>'name', translations->'en'->>'description'
  FROM web_links
  WHERE organisation_id IS NOT NULL AND
    translations->'en'->>'name' != ''
;

INSERT INTO contact_info_data (langcode, entity_id, name, description)
  SELECT 'sv', id % 100000 + 200000, translations->'sv'->>'name', translations->'sv'->>'description'
  FROM web_links
  WHERE organisation_id IS NOT NULL AND
    translations->'sv'->>'name' != ''
;
INSERT INTO contact_info_data (langcode, entity_id, name, description)
  SELECT 'ru', id % 100000 + 200000, translations->'ru'->>'name', translations->'ru'->>'description'
  FROM web_links
  WHERE organisation_id IS NOT NULL AND
    translations->'ru'->>'name' != ''
;
INSERT INTO contact_info_data (langcode, entity_id, name, description)
  SELECT 'se', id % 100000 + 200000, translations->'se'->>'name', translations->'se'->>'description'
  FROM web_links
  WHERE organisation_id IS NOT NULL AND
    translations->'se'->>'name' != ''
;



CREATE TYPE department_type AS enum('department', 'mobile_stop');

CREATE TABLE departments (
  id serial NOT NULL,
  type department_type NOT NULL,
  parent_id int NOT NULL,
  PRIMARY KEY(id),
  FOREIGN KEY(parent_id) REFERENCES organisations(id)
);

CREATE TABLE departments_data (
  entity_id int NOT NULL,
  langcode varchar(2) NOT NULL,
  name varchar(100) NOT NULL,
  description text,

  PRIMARY KEY(entity_id, langcode),
  FOREIGN KEY(entity_id) REFERENCES departments(id) ON DELETE CASCADE
);

INSERT INTO departments (id, type, parent_id)
  SELECT id, 'department', parent_id
  FROM organisations
  WHERE role = 'department' AND parent_id IS NOT NULL
;

INSERT INTO departments_data (entity_id, langcode, name, description)
  SELECT b.entity_id, b.langcode, b.name, b.description
  FROM organisations a
  INNER JOIN organisations_data b ON a.id = b.entity_id
  WHERE a.role = 'department' AND a.parent_id IS NOT NULL
;

UPDATE organisations SET state = -1 WHERE role = 'department' AND parent_id IS NOT NULL;



-- GENERATE WEIGHTS

UPDATE contact_info a
SET weight = sub.pos
FROM (
  SELECT id, row_number() OVER(PARTITION BY attached_to, type, parent_id) pos
  FROM contact_info
  ORDER BY id
) sub
WHERE a.id = sub.id AND a.weight IS NULL
;

UPDATE pictures a
SET weight = sub.pos
FROM (
  SELECT id, row_number() OVER(PARTITION BY parent_id) pos
  FROM pictures
  ORDER BY cover DESC, id
) sub
WHERE a.id = sub.id AND parent_id IS NOT NULL;

UPDATE organisations a SET type = 'archive' FROM organisations_data b WHERE role = 'foreign' AND b.name ILIKE '%arkisto%' AND a.id = b.entity_id AND b.langcode = 'fi';

UPDATE organisations a SET type = 'museum' FROM organisations_data b WHERE role = 'foreign' AND b.name ILIKE '%museo%' AND a.id = b.entity_id AND b.langcode = 'fi';




ALTER TABLE schedules RENAME COLUMN status TO live_status;




UPDATE users SET roles = '["ROLE_ROOT"]' WHERE email like '%@kirjastot.fi';



UPDATE organisations SET state = 0 WHERE role in ('mobile_stop');




ALTER TABLE organisations RENAME COLUMN parent_id TO organisation_id;





-- COMMIT PLACEHOLDER --





ALTER TABLE users ADD COLUMN municipal_account bool NOT NULL DEFAULT false;
UPDATE users SET municipal_account = true WHERE email NOT LIKE '%@kirjastot.fi' AND email NOT LIKE '%@biblioteken.fi';

ALTER TABLE users ADD COLUMN expires date;


-- From now on 'username' will contain the personal name of a user, not an actual username.
ALTER TABLE users DROP CONSTRAINT users_username_key;


-- Nullify duplicate emails to allow for UNIQUE constraint.
UPDATE users SET email = NULL WHERE email = '';
UPDATE users SET email = NULL WHERE id IN (14948, 14621, 15013, 15028, 15029, 14949, 15031);


-- Email addresses are used as logins so they have to be unique.
ALTER TABLE users ADD UNIQUE(email);


ALTER TABLE user_groups ADD COLUMN max_group_admins int;
UPDATE user_groups SET max_group_admins = 3;

ALTER TABLE user_groups ALTER COLUMN max_group_admins SET NOT NULL;

ALTER TABLE users DROP COLUMN roles;
ALTER TABLE users ADD COLUMN roles text[];

UPDATE users SET roles = ARRAY['ROLE_GROUP_MANAGER'];
UPDATE users SET roles = ARRAY['ROLE_ROOT'] WHERE email like '%@kirjastot.fi';

ALTER TABLE user_groups DROP COLUMN roles;
ALTER TABLE user_groups ADD COLUMN roles text[];

UPDATE user_groups SET roles = ARRAY['ROLE_FINNA'] WHERE parent_id = (SELECT id FROM user_groups WHERE role_id = 'finna');




-- COMMIT PLACEHOLDER --





CREATE TABLE one_time_tokens (
  token varchar(64) NOT NULL,
  purpose varchar(64) NOT NULL,
  created timestamptz NOT NULL,
  user_id int NOT NULL,

  PRIMARY KEY(token),
  FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
);

COMMENT ON COLUMN one_time_tokens.token IS 'Hash of the token used in authentication URLs.';
COMMENT ON COLUMN one_time_tokens.purpose IS 'Keyword for identifying what the token is used for.';





ALTER TABLE organisations ALTER COLUMN type DROP NOT NULL;





ALTER TABLE contact_info ADD COLUMN department_id int;
ALTER TABLE contact_info ADD FOREIGN KEY(department_id) REFERENCES departments(id) ON DELETE CASCADE;

ALTER TABLE contact_info ALTER COLUMN parent_id SET NOT NULL;


-- Recreate the view to accommodate the new column.
CREATE OR REPLACE VIEW contact_info_doctrine AS
  SELECT attached_to || ':' || type AS type, id, contact, weight, parent_id, department_id
  FROM contact_info a
;

CREATE OR REPLACE RULE split_type_id AS ON INSERT TO contact_info_doctrine
  DO INSTEAD
    INSERT INTO contact_info (id, attached_to, type, contact, weight, parent_id, department_id)
    VALUES (
      NEW.id,
      left(NEW.type, position(':' IN NEW.type) - 1)::facility_role,
      substring(NEW.type FROM position(':' IN NEW.type) + 1)::contact_info_type,
      NEW.contact,
      NEW.weight,
      NEW.parent_id,
      NEW.department_id
    )
;




-- COMMIT PLACEHOLDER --




CREATE TABLE finna_service_point_bindings (
  parent_id int NOT NULL,
  library_id int,
  service_point_id int,

  PRIMARY KEY(parent_id),
  FOREIGN KEY(parent_id) REFERENCES finna_additions(id) ON DELETE CASCADE,
  FOREIGN KEY(library_id) REFERENCES organisations(id) ON DELETE CASCADE,
  FOREIGN KEY(service_point_id) REFERENCES organisations(id) ON DELETE CASCADE
);

INSERT INTO finna_service_point_bindings (parent_id, library_id) (
  SELECT a.id, a.service_point_id
  FROM finna_additions a
    INNER JOIN organisations b ON a.service_point_id = b.id
    WHERE b.role = 'library'
  )
;

INSERT INTO finna_service_point_bindings (parent_id, service_point_id) (
  SELECT a.id, a.service_point_id
  FROM finna_additions a
    INNER JOIN organisations b ON a.service_point_id = b.id
    WHERE b.role = 'foreign'
  )
;

-- Association is now stored on finna_service_point_bindings.
ALTER TABLE finna_additions DROP COLUMN service_point_id;




-- COMMIT PLACEHOLDER --





UPDATE organisations_data SET description = NULL WHERE description = '<p>&nbsp;</p>';
UPDATE consortiums_data SET description = NULL WHERE description = '<p>&nbsp;</p>';
UPDATE finna_additions_data SET usage_info = NULL WHERE usage_info = '<p>&nbsp;</p>';





-- COMMIT PLACEHOLDER --




DELETE FROM service_instances a
USING organisations b
WHERE a.parent_id = b.id AND b.role NOT IN ('library', 'foreign');




-- COMMIT PLACEHOLDER --




ALTER TYPE facility_role ADD VALUE 'finna_organisation';

ALTER TABLE contact_info ADD COLUMN category varchar(40);
ALTER TABLE contact_info ADD COLUMN finna_organisation_id int;
ALTER TABLE contact_info ADD FOREIGN KEY (finna_organisation_id)
  REFERENCES finna_additions(id) ON DELETE CASCADE;

ALTER TABLE contact_info ALTER COLUMN parent_id DROP NOT NULL;


-- Need this to avoid a complaint about renaming columns.
DROP VIEW contact_info_doctrine;

-- Recreate the view to accommodate the new column.
CREATE OR REPLACE VIEW contact_info_doctrine AS
  SELECT
    attached_to || ':' || type AS type,
    id,
    contact,
    weight,
    category,
    parent_id,
    department_id,
    finna_organisation_id
  FROM contact_info a
;

CREATE OR REPLACE RULE split_type_id AS ON INSERT TO contact_info_doctrine
  DO INSTEAD
    INSERT INTO contact_info (
      id,
      attached_to,
      type,
      contact,
      weight,
      category,
      parent_id,
      department_id,
      finna_organisation_id
    )
    VALUES (
      NEW.id,
      left(NEW.type, position(':' IN NEW.type) - 1)::facility_role,
      substring(NEW.type FROM position(':' IN NEW.type) + 1)::contact_info_type,
      NEW.contact,
      NEW.weight,
      NEW.category,
      NEW.parent_id,
      NEW.department_id,
      NEW.finna_organisation_id
    )
;

DELETE FROM web_links WHERE consortium_id = 2183;

INSERT INTO contact_info (type, id, contact, finna_organisation_id, attached_to, category)
  SELECT 'website', a.id % 100000 + 200000, a.url, a.consortium_id, 'finna_organisation', b.identifier
  FROM web_links a
  LEFT JOIN web_link_groups b ON a.link_group_id = b.id
  WHERE a.consortium_id IS NOT NULL
;

INSERT INTO contact_info_data (langcode, entity_id, name, description)
  SELECT 'fi', id % 100000 + 200000, name, description
  FROM web_links
  WHERE consortium_id IS NOT NULL
;

INSERT INTO contact_info_data (langcode, entity_id, name, description)
  SELECT 'en', id % 100000 + 200000, translations->'en'->>'name', translations->'en'->>'description'
  FROM web_links
  WHERE consortium_id IS NOT NULL AND
    translations->'en'->>'name' != ''
;

INSERT INTO contact_info_data (langcode, entity_id, name, description)
  SELECT 'sv', id % 100000 + 200000, translations->'sv'->>'name', translations->'sv'->>'description'
  FROM web_links
  WHERE consortium_id IS NOT NULL AND
    translations->'sv'->>'name' != ''
;
INSERT INTO contact_info_data (langcode, entity_id, name, description)
  SELECT 'ru', id % 100000 + 200000, translations->'ru'->>'name', translations->'ru'->>'description'
  FROM web_links
  WHERE consortium_id IS NOT NULL AND
    translations->'ru'->>'name' != ''
;
INSERT INTO contact_info_data (langcode, entity_id, name, description)
  SELECT 'se', id % 100000 + 200000, translations->'se'->>'name', translations->'se'->>'description'
  FROM web_links
  WHERE consortium_id IS NOT NULL AND
    translations->'se'->>'name' != ''
;

UPDATE contact_info a
SET weight = sub.pos
FROM (
  SELECT id, row_number() OVER(PARTITION BY attached_to, type, finna_organisation_id) pos
  FROM contact_info
  ORDER BY id
) sub
WHERE a.id = sub.id AND a.weight IS NULL AND a.finna_organisation_id IS NOT NULL
;




-- COMMIT PLACEHOLDER --




ALTER TABLE organisations_data ADD COLUMN email_id int;
ALTER TABLE organisations_data ADD COLUMN homepage_id int;
ALTER TABLE organisations_data ADD COLUMN phone_id int;

ALTER TABLE organisations_data ADD FOREIGN KEY (email_id) REFERENCES contact_info(id);
ALTER TABLE organisations_data ADD FOREIGN KEY (homepage_id) REFERENCES contact_info(id);
ALTER TABLE organisations_data ADD FOREIGN KEY (phone_id) REFERENCES contact_info(id);




-- COMMIT PLACEHOLDER --



WITH insert_entity AS (
  INSERT INTO contact_info (type, attached_to, parent_id, contact)
  SELECT 'email', a.role, a.id, b.email
  FROM organisations a
  INNER JOIN organisations_data b ON a.id = b.entity_id
  WHERE a.role IN ('library', 'foreign') AND COALESCE(b.email, '') <> ''
  RETURNING id AS contact_id, parent_id
)
INSERT INTO contact_info_data (entity_id, langcode, name)
SELECT contact_id, b.langcode, 'Oletussähköposti'
FROM insert_entity a
INNER JOIN organisations_data b ON parent_id = b.entity_id
;

UPDATE organisations_data a
SET email_id = b.id
FROM contact_info b
INNER JOIN contact_info_data c ON b.id = c.entity_id
WHERE a.entity_id = b.parent_id
  AND a.langcode = c.langcode
  AND b.type = 'email'
  AND a.email_id IS NULL
;





WITH insert_entity AS (
  INSERT INTO contact_info (type, attached_to, parent_id, contact)
  SELECT 'website', a.role, a.id, b.homepage
  FROM organisations a
  INNER JOIN organisations_data b ON a.id = b.entity_id
  WHERE a.role IN ('library', 'foreign') AND COALESCE(b.homepage, '') <> ''
  RETURNING id AS contact_id, parent_id
)
INSERT INTO contact_info_data (entity_id, langcode, name)
SELECT contact_id, b.langcode, 'Kirjaston kotisivut'
FROM insert_entity a
INNER JOIN organisations_data b ON parent_id = b.entity_id
;

UPDATE organisations_data a
SET homepage_id = b.id
FROM contact_info b
INNER JOIN contact_info_data c ON b.id = c.entity_id
WHERE a.entity_id = b.parent_id
  AND a.langcode = c.langcode
  AND b.type = 'website'
  AND a.homepage_id IS NULL
;





ALTER TABLE periods ADD COLUMN is_legacy_format bool NOT NULL DEFAULT false;
UPDATE periods SET is_legacy_format = true;




ALTER TYPE contact_info_type ADD VALUE 'finna_organisation:website';
UPDATE contact_info SET type = 'finna_organisation:website' WHERE attached_to = 'finna_organisation';

DROP VIEW contact_info_doctrine;
ALTER TABLE contact_info DROP column attached_to;




-- COMMIT PLACEHOLDER --




ALTER TABLE consortiums RENAME COLUMN logo TO old_logo_filename;
ALTER TABLE consortiums ADD COLUMN logo jsonb;


ALTER TABLE organisations ADD COLUMN photos jsonb;




-- COMMIT PLACEHOLDER --

ALTER TABLE consortiums DROP COLUMN logo;

ALTER TABLE consortiums ADD COLUMN logo_id int;
ALTER TABLE consortiums ADD FOREIGN KEY(logo_id) REFERENCES pictures(id) ON DELETE CASCADE;




ALTER TABLE pictures ADD COLUMN original_name varchar(255);
ALTER TABLE pictures ADD COLUMN dimensions int[2];
ALTER TABLE pictures ADD COLUMN mime_type varchar(100);
ALTER TABLE pictures ADD COLUMN filesize int;



-- COMMIT PLACEHOLDER --

UPDATE consortiums SET old_logo_filename = NULL WHERE old_logo_filename = '';

ALTER TABLE departments ALTER COLUMN type DROP NOT NULL;



-- COMMIT PLACEHOLDER --




ALTER TABLE organisations RENAME COLUMN cached_document TO api_document;
ALTER TABLE consortiums RENAME COLUMN cached_document TO api_document;
ALTER TABLE services RENAME COLUMN cached_document TO api_document;
ALTER TABLE finna_additions RENAME COLUMN cached_document TO api_document;

ALTER TABLE organisations ADD COLUMN api_keywords tsvector;




-- COMMIT PLACEHOLDER --



-- Flip coordinates because they were stored upside-down...
UPDATE addresses SET coordinates = ST_GeomFromText('POINT(' || ST_Y(coordinates::geometry) || ' ' || ST_X(coordinates::geometry) || ')', 4326) WHERE ST_X(coordinates::geometry) > 50;




-- COMMIT PLACEHOLDER --





CREATE TABLE contact_info_groups (
  id serial NOT NULL,
  library_id int NOT NULL,

  PRIMARY KEY(id),
  FOREIGN KEY(library_id) REFERENCES organisations(id) ON DELETE CASCADE
);



CREATE TABLE contact_info_group_data (
  entity_id int NOT NULL,
  langcode varchar(2) NOT NULL,
  name varchar(100) NOT NULL,

  PRIMARY KEY (entity_id, langcode),
  FOREIGN KEY(entity_id) REFERENCES contact_info_groups(id) ON DELETE CASCADE
);



ALTER TABLE contact_info ADD COLUMN group_id int;
ALTER TABLE contact_info ADD FOREIGN KEY(group_id) REFERENCES contact_info_groups(id);




-- COMMIT PLACEHOLDER --




ALTER TYPE department_type ADD VALUE 'meta';
UPDATE departments SET type = 'department' WHERE type IS NULL;




-- COMMIT PLACEHOLDER --





DROP TABLE translations;

CREATE TABLE translations (
  locale varchar(2) NOT NULL,
  domain varchar(40) NOT NULL,
  id text NOT NULL,
  translation text,

  PRIMARY KEY (locale, domain, id)
);





-- COMMIT PLACEHOLDER --





ALTER TABLE organisations ADD COLUMN main_library boolean NOT NULL DEFAULT false;

UPDATE organisations SET main_library = true, type = 'library' WHERE type = 'main_library';

UPDATE organisations SET type = 'library' WHERE type = 'regional';




-- COMMIT PLACEHOLDER --




-- Clear legacy format flag for libraries that don't use self-service schedules.
UPDATE periods SET is_legacy_format = false WHERE parent_id NOT IN (SELECT DISTINCT parent_id FROM periods WHERE section = 'selfservice' AND parent_id IS NOT NULL);






-- COMMIT PLACEHOLDER --





CREATE SEQUENCE user_groups_id_seq START WITH 20000 OWNED BY user_groups.id;





-- COMMIT PLACEHOLDER --




ALTER TABLE periods DROP CONSTRAINT periods_department_id_fkey;
ALTER TABLE periods ADD FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE;

ALTER TABLE schedules DROP CONSTRAINT schedules_department_fkey;
ALTER TABLE schedules ADD FOREIGN KEY (department) REFERENCES departments(id) ON DELETE CASCADE;




-- COMMIT PLACEHOLDER --





DROP TABLE phone_numbers;
DROP TABLE web_links;
DROP TABLE web_link_groups;
DROP TABLE template_references;
DROP TABLE organisations_links;
DROP TABLE external_links;
DROP TABLE services_old;





ALTER TABLE organisations_data DROP CONSTRAINT organisations_data_email_id_fkey;
ALTER TABLE organisations_data DROP CONSTRAINT organisations_data_homepage_id_fkey;
ALTER TABLE organisations_data DROP CONSTRAINT organisations_data_phone_id_fkey;

ALTER TABLE organisations_data ADD FOREIGN KEY (email_id) REFERENCES contact_info(id) ON DELETE CASCADE;
ALTER TABLE organisations_data ADD FOREIGN KEY (homepage_id) REFERENCES contact_info(id) ON DELETE CASCADE;
ALTER TABLE organisations_data ADD FOREIGN KEY (phone_id) REFERENCES contact_info(id) ON DELETE CASCADE;




-- COMMIT PLACEHOLDER --




ALTER TABLE photos_data RENAME TO pictures_data;





-- COMMIT PLACEHOLDER --





ALTER TABLE organisations_data DROP CONSTRAINT organisations_data_email_id_fkey;
ALTER TABLE organisations_data DROP CONSTRAINT organisations_data_homepage_id_fkey;
ALTER TABLE organisations_data DROP CONSTRAINT organisations_data_phone_id_fkey;

ALTER TABLE organisations_data ADD FOREIGN KEY (email_id) REFERENCES contact_info(id) ON DELETE SET NULL;
ALTER TABLE organisations_data ADD FOREIGN KEY (homepage_id) REFERENCES contact_info(id) ON DELETE SET NULL;
ALTER TABLE organisations_data ADD FOREIGN KEY (phone_id) REFERENCES contact_info(id) ON DELETE SET NULL;




-- COMMIT PLACEHOLDER --



UPDATE organisations SET type = 'municipal' WHERE type = 'library';

UPDATE consortiums SET created = modified WHERE created IS NULL;
ALTER TABLE consortiums ALTER created SET NOT NULL;




-- COMMIT PLACEHOLDER --

-- Delete empty address entities created after change to using entities instead of FormData.
DELETE FROM addresses WHERE id IN (SELECT a.id FROM addresses a LEFT JOIN addresses_data b ON a.id = b.entity_id WHERE b.entity_id IS NULL);
