CREATE TABLE sveden_education_contingent(
    id          SERIAL         NOT NULL PRIMARY KEY,
    org_id      INT            NULL,
    spec_id     INT            NULL,
    spec_code   VARCHAR(100)   NULL     COMMENT 'Код',
    spec_name   TEXT           NULL     COMMENT 'Наименование',
    edu_level   TEXT           NULL     COMMENT 'Уровень образования',
    edu_forms   TEXT           NULL     COMMENT 'Формы обучения',
    contingent  INT            NOT NULL COMMENT 'Общая численность обучающихся'
);