CREATE TABLE IF NOT EXISTS `users` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `timeCreated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
   `timeUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `roles` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `name` varchar(255) NOT NULL,
   `timeCreated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
   `timeUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
   UNIQUE KEY `name` (`name`),
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `user_roles` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `roleId` int(11) NOT NULL,
      `userId` int(11) NOT NULL,
      PRIMARY KEY (`id`),
      KEY `roleId` (`roleId`),
      KEY `userId` (`userId`),
      CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`roleId`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
      CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=UTF8;

CREATE TABLE IF NOT EXISTS `permissions` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `name` varchar(255) NOT NULL,
   `timeCreated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
   `timeUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
   UNIQUE KEY `name` (`name`),
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `role_permissions` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `roleId` int(11) NOT NULL,
      `permissionId` int(11) NOT NULL,
      PRIMARY KEY (`id`),
      KEY `roleId` (`roleId`),
      KEY `permissionId` (`permissionId`),
      CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`roleId`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
      CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permissionId`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=UTF8;

CREATE TABLE IF NOT EXISTS `activities` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `method` varchar(32) NOT NULL,
   `regex` varchar(32) NOT NULL,
   `timeCreated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
   `timeUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `permission_activities` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `activityId` int(11) NOT NULL,
      `permissionId` int(11) NOT NULL,
      PRIMARY KEY (`id`),
      KEY `activityId` (`activityId`),
      KEY `permissionId` (`permissionId`),
      CONSTRAINT `permission_activities_ibfk_1` FOREIGN KEY (`activityId`) REFERENCES `activities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
      CONSTRAINT `permission_activities_ibfk_2` FOREIGN KEY (`permissionId`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=UTF8;