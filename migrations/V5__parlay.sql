DELETE FROM kizzang.SportParlayPlaces WHERE parlayCardId NOT IN ( SELECT id FROM kizzang.SportParlayConfig ) ;

ALTER TABLE kizzang.SportParlayPlaces 
ADD foreign key (parlayCardId) references kizzang.SportParlayConfig(id) 
ON UPDATE cascade on DELETE cascade;
