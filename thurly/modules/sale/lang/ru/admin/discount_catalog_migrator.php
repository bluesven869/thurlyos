<?
$MESS["DISCOUNT_CATALOG_MIGRATOR_CONVERT_FAILED"] = "Произошла ошибка при попытке переноса скидок";
$MESS["DISCOUNT_CATALOG_MIGRATOR_PROCESSED_SUMMARY"] = "Перенесено скидок (нарастающим итогом):";
$MESS["DISCOUNT_CATALOG_MIGRATOR_CONVERT_IN_PROGRESS"] = "Еще не все скидки перенесены";
$MESS["DISCOUNT_CATALOG_MIGRATOR_CONVERT_COMPLETE"] = "Перенос скидок завершен";
$MESS["DISCOUNT_CATALOG_MIGRATOR_CONVERT_TITLE"] = "Перенос скидок модуля \"Торговый каталог\"";
$MESS["DISCOUNT_CATALOG_MIGRATOR_CONVERT_TAB"] = "Перенос данных";
$MESS["DISCOUNT_CATALOG_MIGRATOR_CONVERT_TAB_TITLE"] = "Перенос данных";
$MESS["DISCOUNT_CATALOG_MIGRATOR_CONVERT_START_BUTTON"] = "Начать перенос";
$MESS["DISCOUNT_CATALOG_MIGRATOR_CONVERT_STOP_BUTTON"] = "Прервать перенос";
$MESS["DISCOUNT_CATALOG_MIGRATOR_CONVERT_STOP_BUTTON"] = "Прервать перенос";
$MESS["DISCOUNT_CATALOG_MIGRATOR_UNKNOWN_ERROR"] = "Неизвестная ошибка при миграции скидки";
$MESS["DISCOUNT_CATALOG_MIGRATOR_ERROR_REPORT"] = "Не удалось обработать <a href\"#URL#\">#TITLE#</a>: #ERRORS#";
$MESS["DISCOUNT_CATALOG_MIGRATOR_NON_SUPPORTED_FEATURE_DISC_SAVE"] = "Накопительные скидки";
$MESS["DISCOUNT_CATALOG_MIGRATOR_NON_SUPPORTED_FEATURE_DISC_TYPE_SALE"] = "Действие скидки \"Установить цену товара\" ";
$MESS["DISCOUNT_CATALOG_MIGRATOR_NON_SUPPORTED_FEATURE_RELATIVE_ACTIVE_PERIOD"] = "Установлен срок действия накопительных скидок \"Время с момента получения\" ";
$MESS["DISCOUNT_CATALOG_MIGRATOR_NON_SUPPORTED_FEATURE_DISC_CURRENCY_SALE_SITE"] = "Валюта скидки отличается от валюты магазина для этого сайта";
$MESS["DISCOUNT_CATALOG_MIGRATOR_NON_SUPPORTED_TEXT"] = "В данный момент мы не объединяем некоторые виды скидок, вам необходимо обратить на них внимание.<br>
Мы нашли следующие неподдерживаемые виды скидок у вас:<br><br>
";
$MESS["DISCOUNT_CATALOG_MIGRATOR_HELLO_TEXT"] = "Обновлением мы объединяем два основных вида скидок: «Скидки торгового каталога» и «Скидки магазина» в общую очередь последовательного выполнения.<br><br>
Объединение позволит гибко управлять зависимостями между скидками, даст возможность в нужный момент останавливать выполнение скидок или устанавливать нужный приоритет выполнения.<br><br>
В зависимости от количества скидок на вашем проекте, процесс объединения может занять продолжительное время. Выполняйте перенос в наименее нагруженное для вашего магазина время.<br><br>
Эффективной работы вам с объединенными скидками!<br>
";
$MESS['DISCOUNT_CATALOG_MIGRATOR_HELLO_TEXT_FINAL'] = "По окончании объединения скидок, вам необходимо проверить опцию «Прекратить дальнейшее применение скидок» на корректность включения в нужных скидках. Скидки «Торгового Каталога» и скидки «Магазина» будут находиться в одной общей очереди выполнения и могут влиять друг на друга.<br><br>
Перед началом объединения рекомендуем сделать резервную копию вашего проекта и его базы данных.<br><br>
На время объединения мы отключим публичную часть вашего проекта. 
";
$MESS["DISCOUNT_CATALOG_MIGRATOR_PAGE_HELLO_TEXT"] = "<p>Мастер объединения скидок позволит перейти на новый, удобный способ управления скидками с общей сортировкой и приоритетами применимости скидок.</p> 
<p>По окончании работы мастера вы получите готовый список маркетинговых акций, который позволит начать работу со скидками без необходимости изучения системы скидок и сложных настроек.</p> 
<p>Создавайте более гибкие условия скидок для своих клиентов. Занимайтесь маркетингом, придумывайте акции, а сложными рутинными операциями займемся мы!</p>
";
$MESS["DISCOUNT_CATALOG_MIGRATOR_HELLO_TEXT_NEW"] = "Обновлением мы объединяем два основных вида скидок: «Скидки торгового каталога» и «Скидки магазина» в общую очередь последовательного выполнения.<br><br>
Объединение позволит гибко управлять зависимостями между скидками, даст возможность в нужный момент останавливать выполнение скидок или устанавливать нужный приоритет выполнения.<br><br>
В зависимости от количества скидок на вашем проекте, процесс объединения может занять продолжительное время. Выполняйте перенос в наименее нагруженное для вашего магазина время.<br><br>
#CUMULATIVE_PART#
Эффективной работы вам с объединенными скидками!<br>
";
$MESS["DISCOUNT_CATALOG_MIGRATOR_HELLO_TEXT_CUMULATIVE_PART"] = "Вы используете накопительные скидки!<br><br>
<b>Обратите внимание!</b><br><br>
Мы улучшили логику обработки скидок. Теперь можно «Прекратить дальнейшее применение правил» и «Установить приоритет скидки».
В процессе перехода на новую платформу мы перенесем накопительные скидки. Вам потребуется проверить и установить приоритеты скидок и при необходимости, настроить их автоматическое завершение.<br>
По умолчанию мы устанавливаем накопительные скидки с наименьшим приоритетом выполнения, чтобы они выполнялись в последнюю очередь. Если выше есть скидки с настройкой «Прекратить дальнейшее применение правил», то накопительные скидки не применятся.<br><br><br>
";
?>