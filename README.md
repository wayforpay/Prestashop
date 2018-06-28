Установка
1. Содержнимое архива поместить в папку modules/wayforpay
2. Выполнить запрос к бд:
```sql
INSERT INTO ps_module (name, active, version) VALUES ('wayforpay',1,'1.0');
```
3. В админке зайти в modules&services - installed modules и включить модуль
