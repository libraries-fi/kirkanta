CREATE TABLE api_documents (
  type varchar(30) NOT NULL,
  id int NOT NULL,

  common_data jsonb NOT NULL,
  translations json,

  PRIMARY KEY (type, id)
);
