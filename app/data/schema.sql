SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';


-- -----------------------------------------------------
-- Table `participant`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `participant` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `lft` INT(11) NOT NULL,
  `rgt` INT(11) NOT NULL,
  `depth` SMALLINT(6) NOT NULL,
  `eligible_to_level` INT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 1413122
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `rwd_basic`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `rwd_basic` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `usr_rewarded_id` INT(11) UNSIGNED NOT NULL,
  `subject_id` INT(11) NOT NULL,
  `subject_type` VARCHAR(128) NOT NULL,
  `value` DECIMAL(10,4) NOT NULL,
  `level` INT(11) NOT NULL,
  `status` VARCHAR(64) NOT NULL DEFAULT 'pending',
  `status_reason` VARCHAR(128) NULL,
  `is_locked` TINYINT(1) NOT NULL DEFAULT '0',
  `is_final` TINYINT(1) NOT NULL DEFAULT '1',
  `approved_at` INT(11) NULL DEFAULT NULL,
  `created_at` INT(11) NOT NULL,
  `updated_at` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `usr_rewarded_id_UNIQUE` (`subject_id` ASC, `subject_type` ASC, `level` ASC),
  INDEX `fk_rwd_basic_usr_identity1_idx` (`usr_rewarded_id` ASC),
  CONSTRAINT `fk_rwd_basic_usr_identity1`
    FOREIGN KEY (`usr_rewarded_id`)
    REFERENCES `participant` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 15
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `rwd_custom`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `rwd_custom` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `usr_rewarded_id` INT(11) UNSIGNED NOT NULL,
  `subject_id` INT(11) NULL,
  `subject_type` VARCHAR(128) NULL,
  `value` DECIMAL(10,4) NOT NULL,
  `status` VARCHAR(64) NOT NULL DEFAULT 'pending',
  `status_reason` VARCHAR(128) NULL,
  `is_locked` TINYINT(1) NOT NULL,
  `note` TEXT NULL,
  `approved_at` INT(11) NULL DEFAULT NULL,
  `created_at` INT(11) NOT NULL,
  `updated_at` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_rwd_custom_usr_identity1_idx` (`usr_rewarded_id` ASC),
  CONSTRAINT `fk_rwd_custom_usr_identity1`
    FOREIGN KEY (`usr_rewarded_id`)
    REFERENCES `participant` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 5
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `rwd_extra`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `rwd_extra` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `usr_rewarded_id` INT(11) UNSIGNED NOT NULL,
  `subject_id` INT(11) UNSIGNED NOT NULL,
  `subject_type` ENUM('rwdbasic') NOT NULL,
  `value` DECIMAL(10,4) NOT NULL,
  `status` VARCHAR(64) NOT NULL DEFAULT 'pending',
  `status_reason` VARCHAR(128) NULL,
  `is_locked` TINYINT(1) NOT NULL,
  `approved_at` INT(11) NULL DEFAULT NULL,
  `created_at` INT(11) NOT NULL,
  `updated_at` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_rwd_extra_usr_identity1_idx` (`usr_rewarded_id` ASC),
  UNIQUE INDEX `subject_id_UNIQUE` (`subject_id` ASC, `subject_type` ASC, `usr_rewarded_id` ASC),
  CONSTRAINT `fk_rwd_extra_usr_identity1`
    FOREIGN KEY (`usr_rewarded_id`)
    REFERENCES `participant` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `subject`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `subject` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `participant_id` INT(11) UNSIGNED NULL DEFAULT NULL,
  `amount` FLOAT(11) NOT NULL,
  `amount_vat` FLOAT(11) NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  INDEX `fk_participant_idx` (`participant_id` ASC),
  CONSTRAINT `fk_subject_participant`
    FOREIGN KEY (`participant_id`)
    REFERENCES `participant` (`id`)
    ON UPDATE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 5
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
