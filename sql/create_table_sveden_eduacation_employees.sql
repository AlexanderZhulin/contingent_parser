CREATE TABLE sveden_education_employees (
    id          INT DEFAULT AUTO_INCREMENT  NOT NULL PRIMARY KEY,
    org_id      INT                         NOT NULL,
    fio         TEXT                        NOT NULL COMMENT 'ФИО',
    disciplines TEXT                        NOT NULL COMMENT 'Дисциплины',
    -- update_date TIMESTAMP      NOT NULL COMMENT 'Дата изменений',
    -- is_actual   BOOLEAN        NOT NULL COMMENT 'Действительность данных'
);