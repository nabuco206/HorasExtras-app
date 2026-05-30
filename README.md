

php artisan migrate:refresh --seed

php artisan queue:work


TEST
172.17.65.14
docker run --rm -i grafana/k6 run - <stress_test.js


/********/
SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE tbl_bolson_hists;
TRUNCATE TABLE tbl_bolson_tiempos;
TRUNCATE TABLE tbl_seguimiento_solicituds;
TRUNCATE TABLE tbl_solicitud_compensas;
TRUNCATE TABLE tbl_solicitud_hes;

SET FOREIGN_KEY_CHECKS = 1;
/*************************/
