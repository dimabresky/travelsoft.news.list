# travelsoft.news.list

Модифицированный компонент bitrix.news.list

Добавлена возможность фильтрации элементов из настройки параметров компонента.
В параметрах компонента появилась вкладка "Дополнительные параметры фильтрации для вывода элементов" из которой можно фильтровать элементы для нужного Вам вывода.

Также появилась возможность в папке шаблона компонента использовать файл component_prolog.php (наряду с result_modifier.php и component_epilog.php). Данный файл подключается до выполнения component.php. В нем можно выполнять код (например до выполнения кеширования).

