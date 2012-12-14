create database skiresorts;

grant all privileges on skiresorts.* to `skiresorts`@`localhost` identified by 'skiresorts';

use skiresorts;


create table villages (
	id int not null auto_increment primary key,
	name varchar(255),
	created_at int(11) not null,
	updated_at int(11) not null
) engine = myisam default charset=utf8;

create table ski_resorts (
	id int not null auto_increment primary key,
	name varchar(255),
	village_id int not null,
	created_at int(11) not null,
	updated_at int(11) not null,
	index village_id_idx(village_id)
) engine = myisam default charset=utf8;
