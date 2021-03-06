<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\data\ArrayDataProvider;
use yii\jui\Sortable;
use frontend\assets\BanTheCanAsset;

BanTheCanAsset::register($this);
/* @var $this yii\web\View */
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="site-kanbanboard">
    <h1><?= Html::encode($boardTitle) ?></h1>
    <small><em><?= Html::encode($boardDescription) ?></em></small>
    <div id="info"></div>

    <?php
    // Create HTML Div Element for each Ticket using $columnId as an index to the column
    // Tickets are appended to one another and wrapped by a sortable div element
    // This dive then becomes the contents of one table cell
    // They need to be appended to one another as they are evaluated in the loop

    //initialize gridRow array
    foreach ($columnData as $column) {
        $gridRow[$column['attribute']] = [];
    }

    //fill grid row array with tickets
    foreach ($ticketData as $ticketRecord) {
        $widgetId = 'ticketwidget_'. $ticketRecord['ticketId'];
        $gridRow[$ticketRecord['columnId']][] = [
            'content' => $this->render('../ticket/_ticketBlock', ['ticketRecord' => $ticketRecord]),
            'options' => [
                'id' => $widgetId,
                'tag' => 'div',
                'class' => 'ticket-widget',
            ],
        ];
    }

    // Wrap gridRow column contents into a sortable div element.
    foreach ($columnData as $column) {
        $cIndex = $column['attribute'];
        $gridRow[$cIndex] = Sortable::widget([
            'items' => $gridRow[$cIndex],
            'options' => ['id' => 'boardColumn_' . $cIndex, 'tag' => 'div', 'class' => 'board-column'],
            'clientOptions' => [
                'cursor' => 'move',
                'connectWith' => ($cIndex != 6 ? '#boardColumn_' . ($cIndex + 1) : '#boardColumn_1'),
            ],
        ]);
    }

    $dataProvider = new ArrayDataProvider([
        'allModels' => [$gridRow],
        'sort' => false,
        'pagination' => false,
    ]);

    // create Column Data array compatible for consumption by GridView
    // column data and ticket data are related but processed separately
    foreach ($columnData as $column) {
        $gridColumn[] = [
            'attribute' => $column['attribute'],
            // need to learn how to purify HTML but allow the ID,I18N formatter, purifier causes problems
            // somehow somewhere it needs to be configured
            'format' => 'raw', //When USing HTML the ID attribute is removed by the purifier, bad news for Draggable
            'label' => $column['title'],
            'contentOptions' => ['valign' => 'top'],
        ];
    }

    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'summary' => '', //removes total count at the top
        'tableOptions' => [
            'class' => 'table-striped',
        ],
        'columns' => $gridColumn,
    ]);

    ?>
</div>