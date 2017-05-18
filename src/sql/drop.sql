/* load.sql
* sql script file created by Xiongfeng Hu
*/ 

/* drop the "child" table first to avoid FOREIGN KEY constraint*/
/* Drop table MovieActor in database */
DROP TABLE IF EXISTS MovieActor;
/* Drop table MovieDirector in database */
DROP TABLE IF EXISTS MovieDirector;
/* Drop table MovieDirector in database */
DROP TABLE IF EXISTS MovieGenre ;
/* Drop table Review in database */
DROP TABLE IF EXISTS Review;


/* drop the "parent" table after*/
/* Drop table Movie in database */
DROP TABLE IF EXISTS Actor ;
/* Drop table Director in database */
DROP TABLE IF EXISTS Director;
/* Drop table Movie in database */
DROP TABLE IF EXISTS Movie;
/* Drop table MaxMovieID in database */
DROP TABLE IF EXISTS MaxMovieID;
/* Drop table MaxPersonID in database */
DROP TABLE IF EXISTS MaxPersonID;