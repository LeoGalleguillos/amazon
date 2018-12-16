CREATE TABLE `product` (
  `product_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `asin` varchar(10) NOT NULL,
  `title` TEXT NOT NULL,
  `product_group` varchar(255) NOT NULL,
  `binding` varchar(255) DEFAULT NULL,
  `brand` varchar(255) DEFAULT NULL,
  `list_price` float(16,2) DEFAULT NULL,
  `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `hashtags_retrieved` datetime DEFAULT NULL,
  `hi_res_images_retrieved` datetime DEFAULT NULL,
  `similar_retrieved` datetime DEFAULT NULL,
  `video_generated` datetime DEFAULT NULL,
  PRIMARY KEY (`product_id`),
  UNIQUE KEY `asin` (`asin`),
  KEY `product_group_modified` (`product_group`,`modified`),
  KEY `created` (`created`),
  KEY `modified` (`modified`),
  KEY `product_group_binding_modified` (`product_group`,`binding`,`modified`),
  KEY `product_group_brand_modified` (`product_group`,`brand`,`modified`),
  KEY `product_group_binding_brand_modified` (`product_group`,`binding`,`brand`,`modified`),
  KEY `product_group_similar_retrieved` (`product_group`,`similar_retrieved`),
  KEY `hi_res_images_retrieved_video_generated_created` (`hi_res_images_retrieved`,`video_generated`, `created`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8
