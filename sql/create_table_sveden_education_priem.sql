CREATE TABLE sveden_education_priem(
    id          SERIAL         NOT NULL PRIMARY KEY,
    org_id      INT            NULL,
    spec_id     INT            NULL,
    spec_code   VARCHAR(100)   NULL     COMMENT 'Код',
    spec_name   TEXT           NULL     COMMENT 'Наименование',
    edu_level   TEXT           NULL     COMMENT 'Уровень образования',
    edu_forms   TEXT           NULL     COMMENT 'Формы обучения',
    contingent  INT            NOT NULL COMMENT 'Общая численность'
    budget      INT            NOT NULL COMMENT 'Число бюджетных мест',
    avg_score   FLOAT          NOT NULL COMMENT 'Средняя сумма баллов',
    data_hash   VARCHAR(32)    NOT NULL COMMENT 'Хэш записи',
    update_date TIMESTAMP      NOT NULL COMMENT 'Дата последних изменений',
    is_actual   BOOLEAN        NOT NULL COMMENT 'Действительность данных'
);