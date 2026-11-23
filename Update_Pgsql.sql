-- 从旧版（无 email / state 字段）升级到当前结构
-- PostgreSQL 9.6+ 支持 ADD COLUMN IF NOT EXISTS，重复执行不会报错
ALTER TABLE "typecho_links" ADD COLUMN IF NOT EXISTS "email" varchar(50) DEFAULT NULL;
ALTER TABLE "typecho_links" ADD COLUMN IF NOT EXISTS "state" integer DEFAULT 1;
