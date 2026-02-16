-- QubitTerm::TABLE_NAME = term
-- QubitObjectTermRelation::TABLE_NAME = object_term_relation
-- QubitInformationObject::TABLE_NAME = information_object

-- Report on the usabes of taxonomy terms in information objects.
-- This is a simple report that counts the number of times each term is
-- used in information objects. The report is ordered by term id.

SELECT DISTINCT t.id, tin.name, COUNT(i.id) AS use_count
FROM term t INNER JOIN object_term_relation r
                       ON r.term_id=t.id INNER JOIN information_object i ON r.object_id=i.id
            inner join term_i18n tin on t.id=tin.id
WHERE t.taxonomy_id=35 and tin.culture ='ca'
GROUP BY (t.id)
ORDER BY 3 desc

select t.id, tin.name
from taxonomy t inner join taxonomy_i18n tin
                           on t.id = tin.id
where tin.culture = 'ca'

select t.id, tin.name
from term t inner join term_i18n tin
                       on t.id = tin.id
where tin.culture ='ca' and t.taxonomy_id =35
-- order by id


SELECT r.id, t.id, tin.name, i.identifier, ioin.title, r.term_id , r.object_id
FROM term t INNER JOIN object_term_relation r
                       ON r.term_id=t.id INNER JOIN information_object i ON r.object_id=i.id
            INNER join information_object_i18n ioin on ioin.id = i.id
            inner join term_i18n tin on t.id=tin.id
WHERE t.id=52048 and tin.culture ='ca'




