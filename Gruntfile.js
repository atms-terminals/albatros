'use strict';
module.exports = function(grunt) {
    // подгружаем необходимые плагины
    require('load-grunt-tasks')(grunt);

    require('time-grunt')(grunt);
    // конфигурация
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        concurrent: {
            stream1: ['jshint', 'copy', 'bower_concat'],
            stream2: ['concat', 'dev_prod_switch'],
            stream3: ['removelogging', 'sass', 'exec'],
            stream4: ['uglify', 'cssmin', 'htmlmin']
        },
        jshint: {
            options: {
                'browser': true,
                'esnext': true,
                'globals': {
                    '$': true,
                    'console': true,
                    'jQuery': true
                },
                'strict': true,
                'globalstrict': false,
                'curly': true,
                'quotmark': true,
                'eqeqeq': true,
                'eqnull': true,
                'expr': true,
                'latedef': true,
                'onevar': true,
                'noarg': true,
                'node': true,
                'trailing': true,
                'undef': true,
                'unused': true
            },
            '<%== pkg.name %>': {
                src: ['views/js/**/*.js']
            }
        },
        bower_concat: {
            build: {
                dest: {
                    'js': 'tmp/bower.js',
                    'css': 'tmp/bower.css'
                },
                dependencies: {
                    'bootstrap': 'jquery',
                },
                bowerOptions: {
                    relative: false
                }
            }
        },
        concat: {
            options: {
                stripBanners: true,
                banner: '/*! <%= pkg.name %> - v<%= pkg.version %> - <%= grunt.template.today("yyyy-mm-dd") %> */\n',
            },
            build: {
                files: {
                    'tmp/terminal.js': [
                        'bower_components/jquery/dist/jquery.js', 
                        'bower_components/bootstrap/dist/js/bootstrap.js', 
                        'views/js/terminal.js', 
                        'views/js/cashcode.js', 
                        'views/js/rfid.js',
                        'views/kbd/kbd.js'
                        ],
                    'tmp/admin.js': [
                        'bower_components/jquery/dist/jquery.js', 
                        'bower_components/bootstrap/dist/js/bootstrap.js', 
                        'bower_components/moment/moment.js',
                        'bower_components/moment/locale/ru.js',
                        'bower_components/bootstrap-fileinput/js/fileinput.js',
                        'bower_components/eonasdan-bootstrap-datetimepicker/src/js/bootstrap-datetimepicker.js',
                        'views/js/admin.js'
                        ],
                },
            }
        },
        removelogging: {
            build: {
                files: {
                    'tmp/terminal.min.js': 'tmp/terminal.js',
                    'tmp/admin.min.js': 'tmp/admin.js',
                },

                options: {
                    // see below for options. this is optional. 
                }
            }
        },
        uglify: {
            options: {
                stripBanners: true,
                banner: '/*! <%= pkg.name %> - v<%= pkg.version %> - <%= grunt.template.today("yyyy-mm-dd") %> */\n',
            },
            build: {
                files: [{
                    expand: true,
                    cwd: 'tmp',
                    src: '*.min.js',
                    dest: 'release/views/js'
                }]
            }
        },
        sass: {
            build: {
                options: {
                    style: 'expanded'
                },
                files: {
                    'views/css/style-sass.css': 'views/css/style.scss',
                    'views/css/login-sass.css': 'views/css/login.scss',
                    'views/css/flex-sass.css': 'views/css/flex.scss',
                }
            }
        },
        cssmin: {
            build: {
                options: {
                    banner: '/*! <%= pkg.name %> - v<%= pkg.version %> - <%= grunt.template.today("yyyy-mm-dd") %> */\n'
                },
                files: {
                    'release/views/css/login.min.css' : [
                        'bower_components/bootstrap/dist/css/bootstrap.css',
                        'views/css/login*.css',
                    ],
                    'release/views/css/style.min.css' : [
                        'bower_components/bootstrap/dist/css/bootstrap.css',
                        'bower_components/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css',
                        'bower_components/bootstrap-fileinput/css/fileinput.min.css',
                        'views/css/style*.css',
                        'views/css/flex*.css',
                    ]
                }
            }
        },
        htmlmin: {                                     // Task
            dist: {                                      // Target
                options: {                                 // Target options
                    removeComments: true,
                    collapseWhitespace: true
                },
                files: [{
                    expand: true,
                    cwd: 'release/views',
                    src: ['*.php', '*.html'],
                    dest: 'release/views'
                }],
            },
            dev: {                                       // Another target
                options: {                                 // Target options
                    removeComments: true,
                    collapseWhitespace: true
                },
                files: [{
                    expand: true,
                    cwd: 'release/views/include',
                    src: ['*.php', '*.html'],
                    dest: 'release/views/include'
                }],
            }
        },
        watch: {
            sass: {
                files: ['views/css/*.scss', '!views/css/_*.scss'],
                tasks: ['newer:sass'],
            },
            sass2: {
                files: 'views/css/_*.scss',
                tasks: ['sass'],
            },
            js: {
                files: 'views/js/*.js',
                tasks: ['newer:jshint']
            }
        },
        dev_prod_switch: {
            options: {
                // Can be ran as `grunt --env=dev` or ``grunt --env=prod``
                // environment: grunt.option('env') || 'dev', // 'prod' or 'dev'
                environment: 'prod', // 'prod' or 'dev'
                env_char: '#',
                env_block_dev: 'env:dev',
                env_block_prod: 'env:prod'
            },
            build: {
                files: [
                    {
                        expand: true,
                        cwd: '',
                        src: ['*.html', '*.php'],
                        dest: 'release/'
                    },
                    {
                        expand: true,
                        cwd: 'views/',
                        src: ['*.html', '*.php'],
                        dest: 'release/views/'
                    },
                ]
            }
        },
        copy: {
            build: {
                files: [
                {expand: true, flatten: true, src: ['views/img/*'], dest: 'release/views/img/', filter: 'isFile'},
                {expand: true, flatten: true, src: ['views/include/*'], dest: 'release/views/include/', filter: 'isFile'},
                {expand: true, flatten: true, src: ['views/kbd/*.php'], dest: 'release/views/kbd/', filter: 'isFile'},
                {expand: true, flatten: true, src: ['bower_components/bootstrap/fonts/*'], dest: 'release/views/fonts/', filter: 'isFile'},
                {expand: true, src: ['components/PHPExcel/Classes/**'], dest: 'release/', filter: 'isFile'},
                {expand: true, flatten: true, src: ['components/*'], dest: 'release/components/', filter: 'isFile'},
                {expand: true, flatten: true, src: ['controllers/*'], dest: 'release/controllers/', filter: 'isFile'},
                {expand: true, flatten: true, src: ['config/*'], dest: 'release/config/', filter: 'isFile'},
                {expand: true, flatten: true, src: ['models/*'], dest: 'release/models/', filter: 'isFile'},
                {expand: true, flatten: true, src: ['usb/*'], dest: 'release/usb/', filter: 'isFile'},
                {expand: true, flatten: true, src: ['usb/config/*'], dest: 'release/usb/config/', filter: 'isFile'},
                {expand: true, flatten: true, src: ['.htaccess'], dest: 'release/', filter: 'isFile'},
                {expand: true, flatten: true, src: ['start.sh'], dest: 'release/', filter: 'isFile'},
                ]
            },
        },
        exec: {
            echo_something: './phpmin.sh'
        },
        clean: {
            build: ['tmp/'],
            release: ['release/'],
            css: ['views/css/*.map', 'views/css/*.css']
        },
    });

    // регистрируем задачи
    grunt.registerTask('default', ['watch']);
    grunt.registerTask('prod', ['clean', 'bower_concat', 'concurrent:stream1', 'concurrent:stream2', 'concurrent:stream3', 'concurrent:stream4']);
};
