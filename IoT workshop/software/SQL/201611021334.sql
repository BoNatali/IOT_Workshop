-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `mydb` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `mydb` ;

-- -----------------------------------------------------
-- Table `mydb`.`device`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `internet_of_things_workshop`.`device` (
  `id` VARCHAR(4) NOT NULL COMMENT '',
  PRIMARY KEY (`id`)  COMMENT '')
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`device_configuration`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`device_configuration` (
  `device_id` VARCHAR(4) NOT NULL COMMENT '',
  `target_device_id` VARCHAR(4) NOT NULL COMMENT '',
  `color` VARCHAR(45) NOT NULL COMMENT '',
  INDEX `fk_queue_device_idx` (`device_id` ASC)  COMMENT '',
  INDEX `fk_queue_device1_idx` (`target_device_id` ASC)  COMMENT '',
  PRIMARY KEY (`target_device_id`, `device_id`)  COMMENT '',
  CONSTRAINT `fk_queue_device`
    FOREIGN KEY (`device_id`)
    REFERENCES `mydb`.`device` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_queue_device1`
    FOREIGN KEY (`target_device_id`)
    REFERENCES `mydb`.`device` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`queue`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`queue` (
  `timestamp` TIMESTAMP NOT NULL COMMENT '',
  `device_configuration_target_device_id` VARCHAR(4) NOT NULL COMMENT '',
  `device_configuration_device_id` VARCHAR(4) NOT NULL COMMENT '',
  PRIMARY KEY (`timestamp`, `device_configuration_target_device_id`, `device_configuration_device_id`)  COMMENT '',
  INDEX `fk_queue_device_configuration1_idx` (`device_configuration_target_device_id` ASC, `device_configuration_device_id` ASC)  COMMENT '',
  CONSTRAINT `fk_queue_device_configuration1`
    FOREIGN KEY (`device_configuration_target_device_id` , `device_configuration_device_id`)
    REFERENCES `mydb`.`device_configuration` (`target_device_id` , `device_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
