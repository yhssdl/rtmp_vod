INSERT INTO `sea_arcrank` VALUES (1, 0, '开放浏览', 5, 0, 0, ''),
(2, 10, '注册会员', 5, 0, 100, ''),
(3, 50, '中级会员', 5, 300, 200, ''),
(4, 100, '高级会员', 5, 800, 500, '');

INSERT INTO `sea_mytag` VALUES('1','areasearch','地区搜索','1251590919','<a href=\'/{seacms:sitepath}search.php?searchtype=2&searchword=大陆\' target=\"_blank\">大陆</a> \r\n<a href=\'/{seacms:sitepath}search.php?searchtype=2&searchword=香港\'target=\"_blank\">香港</a> \r\n<a href=\'/{seacms:sitepath}search.php?searchtype=2&searchword=台湾\'target=\"_blank\">台湾</a> \r\n<a href=\'/{seacms:sitepath}search.php?searchtype=2&searchword=日本\' target=\"_blank\">日本</a> \r\n<a href=\'/{seacms:sitepath}search.php?searchtype=2&searchword=韩国\' target=\"_blank\">韩国</a> \r\n<a href=\'/{seacms:sitepath}search.php?searchtype=2&searchword=欧美\' target=\"_blank\">欧美</a> \r\n<a href=\'/{seacms:sitepath}search.php?searchtype=2&searchword=其它\' target=\"_blank\">其它</a>');
INSERT INTO `sea_mytag` VALUES('2','yearsearch','按发行年份查看电影','1251509338','<a href=\'/{seacms:sitepath}search.php?searchtype=3&searchword=2009\' target=\"_blank\">2009</a> \r\n<a href=\'/{seacms:sitepath}search.php?searchtype=3&searchword=2008\'target=\"_blank\">2008</a> \r\n<a href=\'/{seacms:sitepath}search.php?searchtype=3&searchword=2007\' target=\"_blank\">2007</a> \r\n<a href=\'/{seacms:sitepath}search.php?searchtype=3&searchword=2006\' target=\"_blank\">2006</a> \r\n<a href=\'/{seacms:sitepath}search.php?searchtype=3&searchword=2005\' target=\"_blank\">2005</a> \r\n<a href=\'/{seacms:sitepath}search.php?searchtype=3&searchword=2004\' target=\"_blank\">2004</a> \r\n<a href=\'/{seacms:sitepath}search.php?searchtype=3&searchword=2003\' target=\"_blank\">2003</a> \r\n<a href=\'/{seacms:sitepath}search.php?searchtype=3&searchword=2002\' target=\"_blank\">2002</a> \r\n<a href=\'/{seacms:sitepath}search.php?searchtype=3&searchword=2001\' target=\"_blank\">2001</a>');
INSERT INTO `sea_mytag` VALUES('3','actorsearch','演员名字','1251590973','<a href=\'/{seacms:sitepath}search.php?searchtype=1&searchword=成龙\' target=\"_blank\">成龙</a> \r\n<a href=\'/{seacms:sitepath}search.php?searchtype=1&searchword=周星驰\'target=\"_blank\">周星驰</a> \r\n<a href=\'/{seacms:sitepath}search.php?searchtype=1&searchword=周润发\'target=\"_blank\">周润发</a> \r\n<a href=\'/{seacms:sitepath}search.php?searchtype=1&searchword=舒淇\' target=\"_blank\">舒淇</a> \r\n<a href=\'/{seacms:sitepath}search.php?searchtype=1&searchword=葛优\' target=\"_blank\">葛优</a> \r\n<a href=\'/{seacms:sitepath}search.php?searchtype=1&searchword=周杰伦\' target=\"_blank\">周杰伦</a> ');
INSERT INTO `sea_mytag` VALUES('4','nav_bottom_banner','导航栏下方通栏广告','1251591021','aaaaaaaaaaaaaaaaaaaaaa\r\n$$$\r\nbbbbbbbbbbbbbbbbbbbbbb\r\n$$$\r\neeeeeeeeeeeeeeeeeeeeee');

INSERT INTO `sea_co_cls` (`id`, `clsname`, `sysclsid`,`cotype`) VALUES (1, '大陆', 0,0);
INSERT INTO `sea_co_cls` (`id`, `clsname`, `sysclsid`,`cotype`) VALUES (2, '香港', 0,0);
INSERT INTO `sea_co_cls` (`id`, `clsname`, `sysclsid`,`cotype`) VALUES (3, '台湾', 0,0);
INSERT INTO `sea_co_cls` (`id`, `clsname`, `sysclsid`,`cotype`) VALUES (4, '日本', 0,0);
INSERT INTO `sea_co_cls` (`id`, `clsname`, `sysclsid`,`cotype`) VALUES (5, '韩国', 0,0);
INSERT INTO `sea_co_cls` (`id`, `clsname`, `sysclsid`,`cotype`) VALUES (6, '欧美', 0,0);
INSERT INTO `sea_co_cls` (`id`, `clsname`, `sysclsid`,`cotype`) VALUES (7, '日韩', 0,0);
INSERT INTO `sea_co_cls` (`id`, `clsname`, `sysclsid`,`cotype`) VALUES (8, '中国', 0,0);

INSERT INTO `sea_myad` VALUES (1,'channel200x200bt','channel200x200bt',1344397254,'测试广告1','document.writeln(\"<div class=\\\"right_b_neirong\\\"><\\/div>\")');
INSERT INTO `sea_myad` VALUES (2,'channel200x200top','channel200x200top',1344397271,'测试广告2','document.writeln(\"<div class=\\\"right_t_neirong\\\"><\\/div>\")');
INSERT INTO `sea_myad` VALUES (3,'channel728x90','channel728x90',1344397299,'测试广告3','document.writeln(\"<div class=\\\"guanggao_neirong\\\"><\\/div>\")');

INSERT INTO `sea_type` VALUES
(1,0,'课堂实录','ketang',1,'channel.html','content.html','play.html','','','',0,'31_1',0),
(2,0,'综艺比赛','zhongyi',2,'channel.html','content.html','play.html','','','',0,'',0),
(3,0,'其它','qita',3,'channel.html','content.html','play.html','','','',0,'',0),
(4,1,'语文','yuwen',4,'channel.html','content.html','play.html','','','',0,'',0),
(5,1,'数学','shuxue',5,'channel.html','content.html','play.html','','','',0,'',0),
(6,1,'英语','yingyu',6,'channel.html','content.html','play.html','','','',0,'',0),
(7,1,'科学','kexue',7,'channel.html','content.html','play.html','','','',0,'',0),
(8,1,'社会','shehui',8,'channel.html','content.html','play.html','','','',0,'',0),
(9,1,'道法','daofa',9,'channel.html','content.html','play.html','','','',0,'',0),
(10,1,'音乐','yinyue',10,'channel.html','content.html','play.html','','','',0,'',0),
(11,1,'美术','meishu',11,'channel.html','content.html','play.html','','','',0,'',0),
(12,1,'劳技','laoji',12,'channel.html','content.html','play.html','','','',0,'',0),
(13,1,'书法','shufa',13,'channel.html','content.html','play.html','','','',0,'',0),
(14,1,'信息','it',14,'channel.html','content.html','play.html','','','',0,'',0),
(15,2,'运动会','yundong',15,'channel.html','content.html','play.html','','','',0,'',0),
(16,2,'艺术节','yishu',16,'channel.html','content.html','play.html','','','',0,'',0),
(17,2,'演讲','yanjiang',17,'channel.html','content.html','play.html','','','',0,'',0),
(18,2,'汇演','huiyan',18,'channel.html','content.html','play.html','','','',0,'',0);

INSERT INTO `sea_member_group` (`gid`, `gname`, `gtype`, `g_auth`, `g_upgrade`) VALUES ('1', '游客用户', '1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16', '1,2', '0');
INSERT INTO `sea_member_group` (`gid`, `gname`, `gtype`, `g_auth`, `g_upgrade`) VALUES ('2', '注册用户', '1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16', '1,2', '0');
INSERT INTO `sea_member_group` (`gid`, `gname`, `gtype`, `g_auth`, `g_upgrade`) VALUES ('3', '超级会员', '1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16', '1,2,3', '100');

INSERT INTO `sea_jqtype` (`tid`,`upid`, `tname`, `ishidden`) VALUES
(1,0, '新授', 0),
(2,0, '复习', 0),
(3,0,'探究', 0),
(4,0, '实验', 0),
(5,0, '活动', 0);