-- phpMyAdmin SQL Dump
-- version 4.5.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: 2016-08-18 11:07:29
-- 服务器版本： 5.7.9
-- PHP Version: 5.6.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `phalcon`
--

-- --------------------------------------------------------

--
-- 表的结构 `logsLogin`
--

CREATE TABLE `logsLogin` (
  `id` int(11) UNSIGNED NOT NULL,
  `userID` int(11) DEFAULT '0',
  `IP` varchar(15) DEFAULT '',
  `location` varchar(32) DEFAULT '',
  `userAgent` varchar(225) DEFAULT '',
  `result` tinyint(4) DEFAULT '0',
  `createdTime` datetime DEFAULT '0000-01-01 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='登录日志';

-- --------------------------------------------------------

--
-- 表的结构 `resources`
--

CREATE TABLE `resources` (
  `id` int(11) UNSIGNED NOT NULL,
  `app` varchar(32) DEFAULT '',
  `name` varchar(32) DEFAULT '',
  `resource` varchar(64) DEFAULT '',
  `type` enum('menu','node') DEFAULT NULL,
  `parent` int(11) DEFAULT '0',
  `sort` int(11) DEFAULT '0',
  `status` tinyint(3) DEFAULT '1',
  `icon` varchar(64) DEFAULT '',
  `remark` varchar(64) DEFAULT '',
  `createdTime` datetime DEFAULT '0000-01-01 00:00:00',
  `updatedTime` datetime DEFAULT '0000-01-01 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='权限部分 - 资源';

--
-- 转存表中的数据 `resources`
--

INSERT INTO `resources` (`id`, `app`, `name`, `resource`, `type`, `parent`, `sort`, `status`, `icon`, `remark`, `createdTime`, `updatedTime`) VALUES
(1000, '', '用户管理', '/users/index', 'menu', 0, 0, 1, '', '', '0000-01-01 00:00:00', '0000-01-01 00:00:00'),
(1001, '', '角色管理', '/roles/index', 'menu', 0, 0, 1, '', '', '0000-01-01 00:00:00', '0000-01-01 00:00:00'),
(1002, '', '资源管理', '/resources/index', 'menu', 0, 0, 1, '', '', '0000-01-01 00:00:00', '0000-01-01 00:00:00');

-- --------------------------------------------------------

--
-- 表的结构 `roleResource`
--

CREATE TABLE `roleResource` (
  `id` int(11) UNSIGNED NOT NULL,
  `roleID` int(11) DEFAULT '0',
  `resourceID` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='权限部分 - 角色&资源';

-- --------------------------------------------------------

--
-- 表的结构 `roles`
--

CREATE TABLE `roles` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(32) DEFAULT '',
  `parent` int(10) DEFAULT '0',
  `remark` varchar(255) DEFAULT '',
  `status` tinyint(3) DEFAULT '1',
  `createdTime` datetime DEFAULT '0000-01-01 00:00:00',
  `udpatedTime` datetime DEFAULT '0000-01-01 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='权限部分 - 角色';

--
-- 转存表中的数据 `roles`
--

INSERT INTO `roles` (`id`, `name`, `parent`, `remark`, `status`, `createdTime`, `udpatedTime`) VALUES
(100, '管理员', 0, '', 1, '0000-01-01 00:00:00', '0000-01-01 00:00:00');

-- --------------------------------------------------------

--
-- 表的结构 `userRole`
--

CREATE TABLE `userRole` (
  `id` int(11) UNSIGNED NOT NULL,
  `userID` int(11) DEFAULT '0',
  `roleID` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='权限部分 - 用户&角色';

-- --------------------------------------------------------

--
-- 表的结构 `users`
--

CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL,
  `username` varchar(32) DEFAULT '',
  `password` varchar(225) DEFAULT '',
  `name` varchar(64) DEFAULT '',
  `status` tinyint(3) DEFAULT '1',
  `createdTime` datetime DEFAULT '0000-01-01 00:00:00',
  `updatedTime` datetime DEFAULT '0000-01-01 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='权限部分 - 用户';

--
-- 转存表中的数据 `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `name`, `status`, `createdTime`, `updatedTime`) VALUES
(10000, 'joe@xxtime.com', '', 'Joe Chu', 1, '0000-01-01 00:00:00', '0000-01-01 00:00:00'),
(10001, 'demo@xxtime.com', '', 'Demo', 1, '0000-01-01 00:00:00', '0000-01-01 00:00:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `logsLogin`
--
ALTER TABLE `logsLogin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userID` (`userID`);

--
-- Indexes for table `resources`
--
ALTER TABLE `resources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `appResource` (`app`,`resource`,`sort`) USING BTREE;

--
-- Indexes for table `roleResource`
--
ALTER TABLE `roleResource`
  ADD PRIMARY KEY (`id`),
  ADD KEY `roleID` (`roleID`),
  ADD KEY `resourceID` (`resourceID`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `userRole`
--
ALTER TABLE `userRole`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userID` (`userID`),
  ADD KEY `roleID` (`roleID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `logsLogin`
--
ALTER TABLE `logsLogin`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- 使用表AUTO_INCREMENT `resources`
--
ALTER TABLE `resources`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1003;
--
-- 使用表AUTO_INCREMENT `roleResource`
--
ALTER TABLE `roleResource`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- 使用表AUTO_INCREMENT `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;
--
-- 使用表AUTO_INCREMENT `userRole`
--
ALTER TABLE `userRole`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- 使用表AUTO_INCREMENT `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10002;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
