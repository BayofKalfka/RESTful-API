/* queries.sql*/
/* This is a .sql file running a SELECT statement to pull all movies that Clint Eastwood took part in*/


/* Give the names of all the movies that 'Clint Eastwood' took part in */
SELECT M.title
FROM  Movie M, MovieActor MA, Actor A 
WHERE  M.id = MA.mid AND MA.aid = A.id AND A.last = 'Eastwood' AND A.first = 'Clint'
UNION
SELECT M.title
FROM  Movie M, MovieDirector MD,Director D
WHERE  M.id = MD.mid AND MD.did = D.id AND D.last = 'Eastwood' AND D.first = 'Clint';