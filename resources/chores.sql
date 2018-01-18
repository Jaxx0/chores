-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema chores
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema chores
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `chores` DEFAULT CHARACTER SET utf8 ;
USE `chores` ;

-- -----------------------------------------------------
-- Table `chores`.`clients`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `chores`.`clients` (
  `client_id` INT NOT NULL AUTO_INCREMENT,
  `client_name` VARCHAR(45) NOT NULL,
  `passwd` MEDIUMTEXT NULL,
  `api_key` MEDIUMTEXT NULL,
  `email` VARCHAR(45) NULL,
  `cell_no` MEDIUMTEXT NULL,
  `location` VARCHAR(45) NULL,
  `first_name` VARCHAR(45) NULL,
  `last_name` VARCHAR(45) NULL,
  `image` VARCHAR(200) NULL,
  `status` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`client_id`),
  UNIQUE INDEX `client_id_UNIQUE` (`client_id` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `chores`.`proffesionals`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `chores`.`proffesionals` (
  `proff_id` INT NOT NULL AUTO_INCREMENT,
  `proff_name` VARCHAR(45) NULL,
  `passwd` VARCHAR(45) NULL,
  `api_key` MEDIUMTEXT NULL,
  `email` VARCHAR(45) NULL,
  `cell_no` VARCHAR(45) NULL,
  `national_id` VARCHAR(45) NOT NULL,
  `location` VARCHAR(45) NULL,
  `availability_status` VARCHAR(45) NULL,
  `image` VARCHAR(200) NULL,
  `first_name` VARCHAR(45) NULL,
  `last_name` VARCHAR(45) NULL,
  `gender` VARCHAR(45) NULL,
  `status` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`proff_id`),
  UNIQUE INDEX `proff_id_UNIQUE` (`proff_id` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `chores`.`client_rating`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `chores`.`client_rating` (
  `client_rating_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_id` INT NOT NULL,
  `proff_id` INT NOT NULL,
  `rating` VARCHAR(45) NULL,
  PRIMARY KEY (`client_rating_id`),
  UNIQUE INDEX `rating_id_UNIQUE` (`client_rating_id` ASC),
  INDEX `fk_client rating_clients1_idx` (`client_id` ASC),
  INDEX `fk_client rating_proffesionals1_idx` (`proff_id` ASC),
  CONSTRAINT `fk_client rating_clients1`
    FOREIGN KEY (`client_id`)
    REFERENCES `chores`.`clients` (`client_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_client rating_proffesionals1`
    FOREIGN KEY (`proff_id`)
    REFERENCES `chores`.`proffesionals` (`proff_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `chores`.`proffessional_rating`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `chores`.`proffessional_rating` (
  `proff_rating_id` INT NOT NULL AUTO_INCREMENT,
  `client_id` INT NOT NULL,
  `proff_id` INT NOT NULL,
  `rating` VARCHAR(45) NULL,
  PRIMARY KEY (`proff_rating_id`),
  UNIQUE INDEX `proff_rating_id_UNIQUE` (`proff_rating_id` ASC),
  INDEX `fk_proffessional rating_clients1_idx` (`client_id` ASC),
  INDEX `fk_proffessional rating_proffesionals1_idx` (`proff_id` ASC),
  CONSTRAINT `fk_proffessional rating_clients1`
    FOREIGN KEY (`client_id`)
    REFERENCES `chores`.`clients` (`client_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_proffessional rating_proffesionals1`
    FOREIGN KEY (`proff_id`)
    REFERENCES `chores`.`proffesionals` (`proff_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `chores`.`proffesional_status`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `chores`.`proffesional_status` (
  `proff_status_id` INT NOT NULL AUTO_INCREMENT,
  `proff_id` INT NOT NULL,
  `proff_text` MEDIUMTEXT NULL,
  `proff_image` MEDIUMTEXT NULL,
  `proff_video` MEDIUMTEXT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`proff_status_id`),
  UNIQUE INDEX `proff_status_id_UNIQUE` (`proff_status_id` ASC),
  INDEX `fk_proffesional status_proffesionals1_idx` (`proff_id` ASC),
  CONSTRAINT `fk_proffesional status_proffesionals1`
    FOREIGN KEY (`proff_id`)
    REFERENCES `chores`.`proffesionals` (`proff_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `chores`.`job_categories`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `chores`.`job_categories` (
  `job_categories_id` INT NOT NULL AUTO_INCREMENT,
  `job_type` VARCHAR(45) NOT NULL,
  `description_text` MEDIUMTEXT NOT NULL,
  `proff_id` INT NULL,
  PRIMARY KEY (`job_categories_id`),
  UNIQUE INDEX `job_categories_id_UNIQUE` (`job_categories_id` ASC),
  INDEX `fk_job categories_proffesionals1_idx` (`proff_id` ASC),
  CONSTRAINT `fk_job categories_proffesionals1`
    FOREIGN KEY (`proff_id`)
    REFERENCES `chores`.`proffesionals` (`proff_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `chores`.`job_post`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `chores`.`job_post` (
  `job_post_id` INT NOT NULL AUTO_INCREMENT,
  `client_id` INT NULL,
  `location` VARCHAR(45) NOT NULL,
  `apartment_name` VARCHAR(45) NOT NULL,
  `house_no` VARCHAR(100) NOT NULL,
  `contact_cell_no` MEDIUMTEXT NOT NULL,
  `job_date` VARCHAR(45) NOT NULL,
  `job_time` VARCHAR(45) NOT NULL,
  `job category` VARCHAR(45) NOT NULL,
  `proff_id` INT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`job_post_id`),
  UNIQUE INDEX `job_post_id_UNIQUE` (`job_post_id` ASC),
  INDEX `fk_job post_clients1_idx` (`client_id` ASC),
  INDEX `fk_job post_proffesionals1_idx` (`proff_id` ASC),
  CONSTRAINT `fk_job post_clients1`
    FOREIGN KEY (`client_id`)
    REFERENCES `chores`.`clients` (`client_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_job post_proffesionals1`
    FOREIGN KEY (`proff_id`)
    REFERENCES `chores`.`proffesionals` (`proff_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `chores`.`job_description`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `chores`.`job_description` (
  `job_description_id` INT NOT NULL AUTO_INCREMENT,
  `job_post_id` INT NOT NULL,
  `text` MEDIUMTEXT NULL,
  `quantity` VARCHAR(45) NULL,
  `image` VARCHAR(200) NULL,
  `amount` VARCHAR(45) NULL,
  `created_at` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`job_description_id`),
  UNIQUE INDEX `job_description_id_UNIQUE` (`job_description_id` ASC),
  INDEX `fk_job description_job post1_idx` (`job_post_id` ASC),
  CONSTRAINT `fk_job description_job post1`
    FOREIGN KEY (`job_post_id`)
    REFERENCES `chores`.`job_post` (`job_post_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `chores`.`payments`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `chores`.`payments` (
  `payment_id` INT NOT NULL AUTO_INCREMENT,
  `client_id` INT NULL,
  `proff_id` INT NULL,
  `job_post_id` INT NULL,
  `job_description_id` INT NULL,
  `payment_date` VARCHAR(45) NOT NULL,
  `payment_time` VARCHAR(45) NOT NULL,
  `transaction_reference` MEDIUMTEXT NOT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`payment_id`),
  UNIQUE INDEX `payment_id_UNIQUE` (`payment_id` ASC),
  INDEX `fk_payments_clients_idx` (`client_id` ASC),
  INDEX `fk_payments_proffesionals1_idx` (`proff_id` ASC),
  INDEX `fk_payments_job post1_idx` (`job_post_id` ASC),
  CONSTRAINT `fk_payments_clients`
    FOREIGN KEY (`client_id`)
    REFERENCES `chores`.`clients` (`client_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_payments_proffesionals1`
    FOREIGN KEY (`proff_id`)
    REFERENCES `chores`.`proffesionals` (`proff_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_payments_job post1`
    FOREIGN KEY (`job_post_id`)
    REFERENCES `chores`.`job_post` (`job_post_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `chores`.`categories`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `chores`.`categories` (
  `category_id` INT NOT NULL AUTO_INCREMENT,
  `category` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`category_id`),
  UNIQUE INDEX `categories_id_UNIQUE` (`category_id` ASC))
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
