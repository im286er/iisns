<?php
namespace frontend\controllers;

use Yii;
use yii\db\Query;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\data\SqlDataProvider;
use app\components\FrontController;
use app\components\Tools;
use app\modules\blog\models\Album;

Yii::setAlias('forum_icon', '@web/uploads/forum/icon/');
Yii::setAlias('avatar', '@web/uploads/user/avatar/');
Yii::setAlias('photo', '@web/uploads/blog/photo/');

/**
 * Explore controller
 */
class ExploreController extends FrontController
{
    public function behaviors()
    {
        return [
            [
                'class' => 'yii\filters\PageCache',
                'only' => ['index', 'forums', 'albums'],
                'duration' => 60 * 60,
                'variations' => [
                    Yii::$app->language,
                    Yii::$app->user->isGuest,
                ],
            ],
        ];
    }
    public $layout = 'explore';

    public function actionIndex()
    {
        $query = new Query;
        $query->select('forum_url,forum_name,forum_desc,forum_icon')
            ->from('{{%forum}}')
            ->orderBy('id DESC');
        $forumResult = Tools::Pagination($query);

        $albums = Yii::$app->db
            ->createCommand('SELECT id, name, cover_id FROM {{%blog_album}} WHERE privilege_type='.Album::TYPE_PUBLIC.' ORDER BY `id` DESC limit 12')
            ->queryAll();
        return $this->render('index', [
            'forums' => $forumResult['result'],
            'pages' => $forumResult['pages'],
            'albums' => $albums,
        ]);
    }

    public function actionForums()
    {
        $query = new Query;
        $query->select('forum_url,forum_name,forum_desc,forum_icon')
            ->from('{{%forum}}')
            ->orderBy('id DESC');
        $forumResult = Tools::Pagination($query);

        return $this->render('forums', [
            'forums' => $forumResult['result'],
            'pages' => $forumResult['pages'],
        ]);
    }

    public function actionAlbums()
    {
        $query = new Query;
        $query->select('id, name, cover_id')
            ->from('{{%blog_album}}')
            ->where('privilege_type=:type', [':type' => Album::TYPE_PUBLIC])
            ->orderBy('id DESC');
        $albumResult = Tools::Pagination($query, 18);

        return $this->render('albums', [
            'albums' => $albumResult['result'],
            'pages' => $albumResult['pages'],
        ]);
    }

    public function actionViewAlbum($id)
    {
        if (($model = Album::findOne($id)) === null) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        if ($model->privilege_type != Album::TYPE_PUBLIC) {
            throw new ForbiddenHttpException('You are not allowed to perform this action.');
        }

        return $this->render('viewAlbum', [
            'model' => $model
        ]);
    }
}