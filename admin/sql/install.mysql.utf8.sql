-- --------------
-- Create tables
--


CREATE TABLE IF NOT EXISTS `#___bm_mailing` (
  `id`                    INT(11)          NOT NULL AUTO_INCREMENT,
  `user_id`               INT(11)          NOT NULL DEFAULT '0',
  `type`                  VARCHAR(64)      NOT NULL DEFAULT '0',
  `naam`                  VARCHAR(255)     NOT NULL DEFAULT '',
  `vervoerder`            VARCHAR(16)      NOT NULL,
  `gewicht`               INT(11)          NOT NULL,
  `aangemeld`             DATETIME         NOT NULL,
  `trace_url`             VARCHAR(255)     NOT NULL,
  `trace_nl`              VARCHAR(255)     NOT NULL,
  `trace_btl`             VARCHAR(255)     NOT NULL,
  `trace_gp`              VARCHAR(32)      NOT NULL,
  `adresnaam`             VARCHAR(100)     NOT NULL,
  `straat`                VARCHAR(200)     NOT NULL,
  `huisnummer`            VARCHAR(50)      NOT NULL,
  `huisnummer_toevoeging` VARCHAR(50)      NOT NULL,
  `postcode`              VARCHAR(12)      NOT NULL,
  `plaats`                VARCHAR(100)     NOT NULL,
  `land`                  VARCHAR(12)      NOT NULL,
  `referentie`            VARCHAR(100)     NOT NULL,
  `klantnummer`           VARCHAR(64)      NOT NULL,
  `importbestand`         VARCHAR(64)      NOT NULL,
  `status`                VARCHAR(255)     NOT NULL DEFAULT '0',
  `state`                 TINYINT(3)       NOT NULL DEFAULT '0',
  `checked_out`           INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `checked_out_time`      DATETIME         NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created`               DATETIME         NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by`            INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `modified`              DATETIME         NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by`           INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `params`                TEXT             NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `trace_nl` (`trace_nl`),
  KEY `user_id` (`user_id`),
  KEY `vervoerder` (`vervoerder`),
  KEY `klantnummer` (`klantnummer`),
  KEY `referentie` (`referentie`)
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8
  AUTO_INCREMENT =1;

CREATE TABLE IF NOT EXISTS `#__bm_massa` (
  `id`               INT(11)          NOT NULL AUTO_INCREMENT,
  `user_id`          INT(11)          NOT NULL DEFAULT '0',
  `type`             VARCHAR(64)      NOT NULL DEFAULT '0',
  `naam`             VARCHAR(255)     NOT NULL DEFAULT '',
  `vervoerder_id`    CHAR(4)          NOT NULL DEFAULT '0',
  `trace`            VARCHAR(255)     NOT NULL,
  `status`           VARCHAR(255)     NOT NULL DEFAULT '0',
  `state`            TINYINT(3)       NOT NULL DEFAULT '0',
  `checked_out`      INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `checked_out_time` DATETIME         NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created`          DATETIME         NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by`       INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `modified`         DATETIME         NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by`      INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `params`           TEXT             NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8
  AUTO_INCREMENT =1;

CREATE TABLE IF NOT EXISTS `#__bm_template` (
  `id`               INT(11)          NOT NULL AUTO_INCREMENT,
  `type`             VARCHAR(64)      NOT NULL DEFAULT '0',
  `onderwerp`        VARCHAR(255)     NOT NULL DEFAULT '',
  `content`          TEXT             NOT NULL,
  `state`            TINYINT(3)       NOT NULL DEFAULT '0',
  `checked_out`      INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `checked_out_time` DATETIME         NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created`          DATETIME         NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by`       INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `modified`         DATETIME         NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by`      INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `params`           TEXT             NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8
  AUTO_INCREMENT =1;

CREATE TABLE IF NOT EXISTS `#__bm_maillog` (
  `id`         INT(11)          NOT NULL AUTO_INCREMENT,
  `mailing_id` INT(11)          NOT NULL,
  `user_id`    INT(11)          NOT NULL,
  `massa_id`   INT(11)          NOT NULL,
  `event`      VARCHAR(64)      NOT NULL DEFAULT '0',
  `ontvangers` VARCHAR(255)     NOT NULL DEFAULT '',
  `onderwerp`  VARCHAR(255)     NOT NULL DEFAULT '',
  `tekst`      TEXT             NOT NULL,
  `created`    DATETIME         NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8
  AUTO_INCREMENT =1;


CREATE TABLE IF NOT EXISTS `#__bm_download` (
  `id`          INT(11)      NOT NULL AUTO_INCREMENT,
  `hash`        VARCHAR(255) NOT NULL,
  `filePath`    VARCHAR(255) NOT NULL,
  `user_id`     INT(11)      NOT NULL,
  `privateFile` INT(11)      NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8
  AUTO_INCREMENT =1;