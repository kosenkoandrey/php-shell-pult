<?
exec('mkdir -m 755 ' . $dist . '/logs');

exec('cp -R ' . ROOT . '/protected ' . $dist . '/protected');
exec('cp -R ' . ROOT . '/public ' . $dist . '/public');

exec('cp ' . ROOT . '/app.php ' . $dist . '/app.php');
exec('cp ' . ROOT . '/init.php ' . $dist . '/init.php');

exec('cp -f ' . $src . '/custom/conf.php ' . $dist . '/conf.php');
exec('cp -f ' . $src . '/custom/protected/modules/DB/conf.php ' . $dist . '/protected/modules/DB/conf.php');
exec('cp -f ' . $src . '/custom/protected/modules/SendThis/conf.php ' . $dist . '/protected/modules/SendThis/conf.php');
exec('cp -f ' . $src . '/custom/protected/modules/SendThis/daemon/conf.json ' . $dist . '/protected/modules/SendThis/daemon/conf.json');
exec('cp -f ' . $src . '/custom/protected/render/admin/widgets/header.php ' . $dist . '/protected/render/admin/widgets/header.php');
exec('cp -f ' . $src . '/custom/protected/render/core/widgets/template/header.php ' . $dist . '/protected/render/core/widgets/template/header.php');
exec('cp -f ' . $src . '/custom/public/ui/img/logo.png ' . $dist . '/public/ui/img/logo.png');

exec('cp -f ' . $src . '/custom/protected/render/mail/spamreport.php ' . $dist . '/protected/render/mail/spamreport.php');
exec('cp -f ' . $src . '/custom/protected/render/tunnels/unsubscribe.php ' . $dist . '/protected/render/tunnels/unsubscribe.php');
exec('cp -f ' . $src . '/custom/protected/render/users/restore.php ' . $dist . '/protected/render/users/restore.php');
exec('cp -f ' . $src . '/custom/protected/render/users/unsubscribe.php ' . $dist . '/protected/render/users/unsubscribe.php');

exec('cp -f ' . $src . '/custom/protected/render/analytics/admin/dashboard/index.php ' . $dist . '/protected/render/analytics/admin/dashboard/index.php');
exec('cp -f ' . $src . '/custom/protected/render/comments/admin/dashboard/index.php ' . $dist . '/protected/render/comments/admin/dashboard/index.php');
exec('cp -f ' . $src . '/custom/protected/render/likes/admin/dashboard/index.php ' . $dist . '/protected/render/likes/admin/dashboard/index.php');
exec('cp -f ' . $src . '/custom/protected/render/logs/admin/dashboard/index.php ' . $dist . '/protected/render/logs/admin/dashboard/index.php');