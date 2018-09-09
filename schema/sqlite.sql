DROP TABLE IF EXISTS "log";
CREATE TABLE "log" (
    "log_id" INTEGER PRIMARY KEY NOT NULL,
    "channel" TEXT NOT NULL,
    "level" INTEGER NOT NULL,
    "datetime" TEXT NOT NULL,
    "message" BLOB NOT NULL,
    "context" BLOB NOT NULL,
    "extra" BLOB NOT NULL
);
