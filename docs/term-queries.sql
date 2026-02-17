-- QubitTerm::TABLE_NAME = term
-- QubitObjectTermRelation::TABLE_NAME = object_term_relation
-- QubitInformationObject::TABLE_NAME = information_object

-- Report on the usages of taxonomy terms in information objects.
-- This is a simple report that counts the number of times each term is
-- used in information objects. The report is ordered by term id.

SELECT DISTINCT t.id, tin.name, s.slug, COUNT(i.id) AS use_count
FROM term t
         INNER JOIN object_term_relation r
                    ON r.term_id = t.id
         INNER JOIN information_object i ON r.object_id = i.id
         inner join term_i18n tin on t.id = tin.id
         inner join slug s on t.id = s.object_id
WHERE t.taxonomy_id = 35
  and tin.culture = 'ca'
GROUP BY (t.id)
ORDER BY 3 desc

select t.id, tin.name
from taxonomy t
         inner join taxonomy_i18n tin
                    on t.id = tin.id
where tin.culture = 'ca'

select t.id, tin.name
from term t
         inner join term_i18n tin
                    on t.id = tin.id
where tin.culture = 'ca'
  and t.taxonomy_id = 35
-- order by id

-- obtener los objetos relacionados con un term concreto
SELECT t.id term_id, tin.name, ioin.title, r.object_id
FROM term t
         INNER JOIN object_term_relation r ON r.term_id = t.id
         inner join term_i18n tin on t.id = tin.id
         INNER JOIN information_object i ON r.object_id = i.id
         INNER join information_object_i18n ioin on ioin.id = i.id and tin.culture = ioin.culture
WHERE t.id = :t_id -- 52136
  and tin.culture = :culture
-- 'ca'

-- obtener el term.id de un slug de un term concreto
select object_id
from slug
where slug = :slug

-- obtener el term id de una cultura y slug
select t.id, tin.name, tin.culture
from term t
         inner join term_i18n tin on t.id = tin.id
         inner join slug s on s.object_id = t.id
where s.slug = :slug -- 'actes-culturals-3'
  and culture = :culture
-- 'ca'

-- obtener el term id de una cultura y nombre de termino
select t.id, s.slug, tin.name, tin.culture
from term t
         inner join term_i18n tin on t.id = tin.id
         inner join slug s on s.object_id = t.id
where tin.name COLLATE utf8mb4_general_ci = :name -- 'Actes culturals'
  and culture = :culture -- 'ca'


update object_term_relation
FROM term t
    INNER JOIN object_term_relation r
on r.term_id = t.id
    inner join term_i18n tin on t.id = tin.id
    INNER JOIN information_object i ON r.object_id = i.id
    INNER join information_object_i18n ioin on ioin.id = i.id and tin.culture = ioin.culture
    set term_id = :new_term_id
WHERE t.id = :old_term_id -- 52136
  and tin.culture = :culture
