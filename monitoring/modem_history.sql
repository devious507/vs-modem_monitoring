DROP TABLE modem_history;
CREATE TABLE modem_history (mac varchar(12) NOT NULL, fwdrx decimal(5,1), fwdsnr decimal(5,1), revtx decimal(5,1), revrx decimal(5,1), revsnr decimal(5,1), ip varchar(15), time timestamp default now(), primchannel varchar(10), interface varchar(10), firstcontact timestamp,  PRIMARY KEY (mac));
