CREATE TABLE service_tree (
  id serial NOT NULL PRIMARY KEY,
  parent_id int,
  group_id int,
  name varchar(120) NOT NULL,
  state int NOT NULL DEFAULT 0,
  sticky boolean NOT NULL DEFAULT false,

  FOREIGN KEY (parent_id) REFERENCES service_tree(id) ON DELETE SET NULL
);

CREATE INDEX ON service_tree (state);

CREATE TABLE service_tree_items (
  id serial NOT NULL PRIMARY KEY,
  service_id int NOT NULL,
  category_id int NOT NULL,

  FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES service_tree(id)
);
