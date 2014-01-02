DROP TABLE flap_logging;
CREATE TABLE flap_logging (
	entrytime timestamp default now(),
	mac char(14),
	upstream char(20),
	ins integer,
	hit integer,
	miss integer,
	crc integer,
	p_adj integer,
	flap integer,
	logtime varchar(30),
	miss_pct integer,
	maxed boolean,
	p_adjusting boolean
);
