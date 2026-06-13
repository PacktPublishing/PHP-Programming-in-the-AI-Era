CREATE TABLE gen_ai_cache (
  `cache_key` char(64) NOT NULL,
  `response` text NOT NULL,
  `ttl_exp` int(16) DEFAULT 0,
  PRIMARY KEY (`cache_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
