# Пакет для формирования дополнительных индексов

Задачей данного пакета является создание индексов со сложными условиями, в том числе с теми что изменяются со временем. Примером может служить сущность с параметрами времени начала и окончания её активности. В зависимости от этого она может добавляться или удаляться из определённых индексов. В качестве второго примера будет индекс распределения сущностей по возрастной категории их авторов.

Для упрощения использования пакета в него добавлен компонент для хранения представлений сущности. Его использование позволяет получать по индексу не только идентификаторы сущностей но и конкретные их представления. Например, анонс материала, состоящий из картинки, заголовка, лида, автора, иконки автора. Это представление требует достаточно разной информации и, следовательно, когда оно уже подготовленно и его не надо формировать, мы получаем экономию ресурсов сервера.

## Фасады

Предназначены для использования в приложении. Каждый из них используется как публичное API для конкретной задачи.

* ```ServiceFacade``` - фасад для использования библиотеки в приложении.
* ```BackendFacade``` - API для фонового процесса (может быть расположен и на другом сервере).

## Компоненты пакета

 1. ```Accessory``` - общие типажи (traits).
 2. ```Action``` - классы определяющие порядок действий при вызове API методов.
 3. ```Bus``` - адаптер внешней шины для обмена сообщениями между клиентским приложением и фоновым процессом сервиса.
 4. ```Configuration``` - классы для создания дерева объектов по данным из некой конфигурации.
 5. ```Dispatcher``` - менеджер событий пакета.
 6. ```Exception``` - интерфейсы для дополнительной группировки исключений компонентов.
 7. ```Index``` - компонент отвечающий за хранение, получение и обновление записей индексов.
 8. ```Integration``` - классы для интеграции пакета с некоторыми DI контейнерами.
 9. ```Regulation``` - менеджер определения принадлежности сущности к конкретным индексам и представлениям.
10. ```Scheduler``` - планировщик заданий на будущее время.
11. ```Source``` - компонент для взаимодействия с внешними источниками сущностей.
12. ```Strategy``` - набор стратегий вызовов действий для _ServiceFacade_ (обращаться к хранилищу или к командной шине).
13. ```Transaction``` - вспомогательный компонент, отвечающий за транзакционность действий с хранилищами данных.
14. ```View``` - компонент отвечающий за формирование и хранение представлений сущности.

## Интеграция

* Для начала необходимо определить или написать классы взаимодействия с адаптерами и хранилищами.
* После этого подключаем классы пакета к используемому DI контейнеру.
* Создаём тип сущности и пишем классы для работы с ней (нормализаторы, инструкции, представления).
* По необходимости добавляем декораторы и слушателей для компонентов пакета.

---------------

_Version 0.7.1_