# 配置文件
首次安装调试时,需要将配置目录下config.example.ini 修改为config.ini

1. 不需要权限控制时 修改/app/config/services.php中的dispatcher配置 注释掉SecurityPlugin配置以及相邻两行,即取消attach
2. 配置文件中setting项目里的RBAC为true时,使用此系统自带的RBAC权限控制, RBAC为false时使用权限控制中心的权限控制
3. 配置文件中db_backend为后端RBAC使用的数据库配置