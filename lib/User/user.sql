
-- template for the user table.

CREATE TABLE m_users (
	u_id            INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	u_alias         VARCHAR(16),
	u_email         VARCHAR(64),
	u_email_confirm VARCHAR(32),
	u_phash         VARCHAR(128),
	u_psand         VARCHAR(128),
	u_jtime         INT UNSIGNED,
	u_ltime         INT UNSIGNED,
	u_admin         BOOL
);

CREATE INDEX u_alias ON m_users(u_alias);
CREATE INDEX u_email ON m_users(u_email);
