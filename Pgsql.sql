CREATE TABLE "typecho_links" (
  "lid" serial NOT NULL,
  "name" varchar(50) DEFAULT NULL,
  "url" varchar(200) DEFAULT NULL,
  "sort" varchar(50) DEFAULT NULL,
  "email" varchar(50) DEFAULT NULL,
  "image" varchar(200) DEFAULT NULL,
  "description" varchar(200) DEFAULT NULL,
  "user" varchar(200) DEFAULT NULL,
  "state" integer DEFAULT 1,
  "order" integer DEFAULT 0,
  PRIMARY KEY ("lid")
);
