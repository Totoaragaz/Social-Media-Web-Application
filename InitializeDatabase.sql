create database therock;

use therock;

create table profile
(
    Username varchar(30) primary key,
    Password varchar(50) not null,
    ProfilePicture varchar(255) not null default 'blankprofilepicture.jpg',
    Bio varchar(255),
    darkmode tinyint default 0,
    strikes int default 0
);

create table posts
(
    Id int primary key AUTO_INCREMENT,
    Username varchar(30) not null,
    Text varchar(255),
    Image varchar(255) default '',
    time datetime default CURRENT_TIMESTAMP(),
    foreign key(Username) references profile(Username)
);

create table admins
(
    username varchar(30) primary key,
    foreign key (username) references profile(Username)
);

create table banned
(
    Username varchar(30) primary key,
    reason varchar(255),
    foreign key(Username) references profile(Username)
);

create table blocked
(
    Username1 varchar(30),
    Username2 varchar(30),
    primary key(Username1,Username2),
    foreign key(Username1) references profile(Username),
    foreign key(Username2) references profile(Username)
);

create table comments
(
    cid int primary key AUTO_INCREMENT,
    pid int not null,
    Username varchar(30) not null,
    Text varchar(255),
    Image varchar(255) default '',
    time datetime not null default CURRENT_TIMESTAMP(),
    FOREIGN key (pid) REFERENCES posts(Id),
    foreign key(Username) references profile(Username)
);

create table friendrequests
(
    Username1 varchar(30),
    Username2 varchar(30),
    primary key(Username1,Username2),
    foreign key(Username1) references profile(Username),
    foreign key(Username2) references profile(Username)
);

create table friends
(
    Username1 varchar(30),
    Username2 varchar(30),
    primary key(Username1,Username2),
    foreign key(Username1) references profile(Username),
    foreign key(Username2) references profile(Username)
);

create table likedcomments
(
    cid int,
    Username varchar(30),
    likeValue int,
    primary key(cid,Username),
    foreign key(cid) references comments(cid),
    foreign key(Username) references profile(Username)
);

create table likedposts
(
    pid int,
    Username varchar(30),
    likeValue int,
    primary key(pid,Username),
    foreign key(pid) references posts(Id),
    foreign key(Username) references profile(Username)
);

create table reportedcomments
(
    reported int,
    reporter varchar(30),
    primary key(reported,reporter),
    foreign key(reported) references comments(cid),
    foreign key(reporter) references profile(Username)
);

create table reportedposts
(
    reported int,
    reporter varchar(30),
    primary key(reported,reporter),
    foreign key(reported) references posts(Id),
    foreign key(reporter) references profile(Username)
);

create table reportedusers
(
    reported varchar(30),
    reporter varchar(30),
    primary key(reported,reporter),
    foreign key(reported) references profile(Username),
    foreign key(reporter) references profile(Username)
);