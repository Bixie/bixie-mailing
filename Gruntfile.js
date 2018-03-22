/**
 * Created by Matthijs on 8-3-14.
 */

module.exports = function (grunt) {
    "use strict";

    // Configuration goes here
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        // Metadata
        meta: {
            pluginPath: 'default/plugins',
            modulePath: 'default/modules',
            componentAdmin: 'admin',
            componentFront: 'front',
            jsRootPath: 'front/assets/js',
            jsSrcPath: 'front/assets/js/src',
            languagePath: 'default/language',
            buildPath: 'build',
            tempPath: 'tmp',
            langs: ['nl-NL']
        },
        copy: {
            // copy component folder to temp folder
            component: {
                files: [
                    {
                        expand: true,
                        cwd: '',
                        src: ['administrator/**', 'frontend/**'],
                        dest: '<%= meta.tempPath %>/<%= meta.componentPath %>'
                    },
                    {
                        expand: true,
                        cwd: '<%= meta.componentAdmin %>',
                        src: ['bixmailing.xml', 'install.script.php'],
                        dest: '<%= meta.tempPath %>/<%= meta.componentPath %>'
                    },
                    {
                        expand: true,
                        cwd: '',
                        src: ['README.md'],
                        dest: '<%= meta.tempPath %>'
                    }
                ]
            },
            // copy repofolders to temp folder
            addons: {
                files: [
                    {
                        expand: true,
                        cwd: '<%= meta.pluginPath %>',
                        src: ['**'],
                        dest: '<%= meta.tempPath %>/<%= meta.pluginPath %>'
                    },
                    {
                        expand: true,
                        cwd: '<%= meta.modulePath %>',
                        src: ['**'],
                        dest: '<%= meta.tempPath %>/<%= meta.modulePath %>'
                    }
                ]
            },
            // copy repofolders to temp folder
            langsource: {
                files: [
                    {
                        expand: true,
                        cwd: '<%= meta.languagePath %>',
                        src: ['**'],
                        dest: '<%= meta.tempPath %>/<%= meta.languagePath %>'
                    }
                ]
            },
            // copy modules folder to temp folder
            overrides: {
                files: []
            },
            // copy modules folder to temp folder
            language: {
                files: []
            }
        },
        concat: {
            options: {
                separator: ';'
            },
            basejs: {
                files: {
                    '<%= meta.jsSrcPath %>/concat/<%= pkg.name %>.front.js': ['<%= meta.jsSrcPath %>/*.js', '<%= meta.jsSrcPath %>/front/*.js'],
                    '<%= meta.jsSrcPath %>/concat/<%= pkg.name %>.admin.js': ['<%= meta.jsSrcPath %>/*.js', '<%= meta.jsSrcPath %>/admin/*.js'],
                    '<%= meta.jsSrcPath %>/concat/uploader.js': [
                        '<%= meta.jsRootPath %>/uploader/jquery.ui.widget.js',
                        '<%= meta.jsRootPath %>/uploader/jquery.iframe-transport.js',
                        '<%= meta.jsRootPath %>/uploader/jquery.fileupload.js',
                        '<%= meta.jsRootPath %>/uploader.js'
                    ]
                }
            }
        },
        uglify: {
            options: {
                banner: '/*! <%= pkg.name %> <%= grunt.template.today("dd-mm-yyyy H:MM:ss") %> */\n'
            },
            rootjs: {
                files: {
                    '<%= meta.componentFront %>/assets/js/bixuserform.min.js': '<%= meta.jsRootPath %>/bixuserform.js',
                    '<%= meta.componentFront %>/assets/js/bixtools.min.js': '<%= meta.jsRootPath %>/bixtools.js',
                    '<%= meta.componentFront %>/assets/js/ajaxsubmit.min.js': '<%= meta.jsRootPath %>/ajaxsubmit.js',
                    '<%= meta.componentFront %>/assets/js/uploader.min.js': '<%= meta.jsSrcPath %>/concat/uploader.js'
                }
            },
            basejs: {
                files: {
                    '<%= meta.componentFront %>/assets/js/<%= pkg.name %>.front.min.js': '<%= meta.jsSrcPath %>/concat/<%= pkg.name %>.front.js',
                    '<%= meta.componentFront %>/assets/js/<%= pkg.name %>.admin.min.js': '<%= meta.jsSrcPath %>/concat/<%= pkg.name %>.admin.js'
                }
            }
        },
        watch: {
            scripts: {
                files: '<%= meta.jsSrcPath %>/**/*.js',
                tasks: ['preparejs'],
                options: {
                    debounceDelay: 5000
                }
            }
        },
        compress: {},
        // remove temporal files
        clean: {
            unpacked: [
                '<%= meta.tempPath %>/<%= meta.componentPath %>/administrator/bixmailing.xml',
                '<%= meta.tempPath %>/<%= meta.componentPath %>/administrator/install.script.php'
            ],
            temp: ['<%= meta.tempPath %>/**/*']
        }
    });

    // Load plugins here
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-compress');
    grunt.loadNpmTasks('grunt-contrib-clean');

    // Define your tasks here
    // register default task
    grunt.registerTask('default', 'Prepares BixieMailing Packages', function () {

        // execute in order
        grunt.task.run('preparejs');
//        grunt.task.run('copy:addons');
//        grunt.task.run('copy:langsource');
//        grunt.task.run('process_component');
//        grunt.task.run('clean:unpacked');
//        grunt.task.run('process_plugins');
//        grunt.task.run('process_modules');
//        grunt.task.run('copy:language');
//        grunt.task.run('compress');
//        grunt.task.run('clean:temp');
    });
    grunt.registerTask('preparejs', ['concat','uglify']);
    grunt.registerTask('watchjs', ['watch:scripts']);

    //process_component task
    grunt.registerTask('process_component', 'pack comp dirs, copy xml', function () {

        // get current configs
        var compress = grunt.config.get('compress') || {},
            copy = grunt.config.get('copy') || {},
            langs = grunt.config.get('meta.langs');

        // add language files
        langs.forEach(function (lang) {
            copy.language.files.push({
                expand: true,
                cwd: '<%= meta.tempPath %>/<%= meta.languagePath %>/administrator/' + lang + '/',
                src: [lang + '.<%= pkg.name %>.ini', lang + '.<%= pkg.name %>.sys.ini'],
                dest: '<%= meta.tempPath %>/<%= meta.componentPath %>/administrator/' + lang + '/'
            });
            copy.language.files.push({
                expand: true,
                cwd: '<%= meta.tempPath %>/<%= meta.languagePath %>/' + lang + '/',
                src: [lang + '.<%= pkg.name %>.ini'],
                dest: '<%= meta.tempPath %>/<%= meta.componentPath %>/frontend/' + lang + '/'
            });
        });

        // set the compress config for component
        compress.component = {
            options: {
                archive: '<%= meta.buildPath %>/component/<%= pkg.name %>_<%= pkg.version %>.zip',
                mode: 'zip'
            },
            files: [
                {
                    expand: true,
                    cwd: '<%= meta.tempPath %>/<%= meta.componentPath %>',
                    src: ['**'],
                    dest: ''
                }
            ]
        };

        // save the new configs
        grunt.config.set('compress', compress);
        grunt.config.set('copy', copy);
    });

    //process_plugins task
    grunt.registerTask('process_plugins', 'iterates over all plugins', function () {

        // get current configs
        var compress = grunt.config.get('compress') || {},
            copy = grunt.config.get('copy') || {},
            baseDirs = ['bixprintshop', 'bixprintshop_attrib', 'bixprintshop_betaal', 'bixprintshop_machine', 'bixprintshop_mail',
                'bixprintshop_mail', 'bixprintshop_order', 'bixprintshop_vervoer',
                'system', 'content', 'search', 'user'],
            root = [],
            langs = grunt.config.get('meta.langs');

        // get plugins rootdirs
        baseDirs.forEach(function (baseDir) {
            root.push(grunt.template.process('<%= meta.tempPath %>/<%= meta.pluginPath %>/' + baseDir + '/') + '*');
        });

        // iterate trough plugins directories
        grunt.file.expand(root).forEach(function (dir) {
            // skip files
            if (!grunt.file.isDir(dir)) {
                return;
            }

            var plugin = dir.replace(/\\/g, '/').replace(/.*\//, ''),
                parent_dir = dir.replace('/' + plugin, '').replace(/\\/g, '/').replace(/.*\//, ''),
                langfileBase = '.plg_' + parent_dir + '_' + plugin;

            // add language files
            langs.forEach(function (lang) {
                copy.language.files.push({
                    expand: true,
                    cwd: '<%= meta.tempPath %>/<%= meta.languagePath %>/administrator/' + lang + '/',
                    src: [lang + langfileBase + '.ini', lang + langfileBase + '.sys.ini'],
                    dest: dir + '/' + lang + '/'
                });
            });

            // set the compress config for this plugin
            compress[parent_dir + plugin] = {
                options: {
                    archive: '<%= meta.buildPath %>/plugins/' + parent_dir + '/' + parent_dir + '-' + plugin + '_<%= pkg.version %>.zip',
                    mode: 'zip'
                },
                files: [
                    {
                        expand: true,
                        cwd: dir,
                        src: ['**'],
                        dest: ''
                    }
                ]
            };
        });

        // save the new configs
        grunt.config.set('compress', compress);
        grunt.config.set('copy', copy);
    });

    //process_modules task
    grunt.registerTask('process_modules', 'iterates over all modules', function () {

        // get current configs
        var compress = grunt.config.get('compress') || {},
            copy = grunt.config.get('copy') || {},
            langs = grunt.config.get('meta.langs'),
        // get modules rootdirs
            root = [
                grunt.template.process('<%= meta.tempPath %>/<%= meta.modulePath %>/administrator/') + '*',
                grunt.template.process('<%= meta.tempPath %>/<%= meta.modulePath %>/frontend/') + '*'
            ];

        // iterate trough modules directories
        grunt.file.expand(root).forEach(function (dir) {
            // skip files
            if (!grunt.file.isDir(dir)) {
                return;
            }

            var module = dir.replace(/\\/g, '/').replace(/.*\//, ''),
                parent_dir = dir.replace('/' + module, '').replace(/\\/g, '/').replace(/.*\//, ''),
                langfileBase = '.' + module,
                adminDir = parent_dir === 'administrator' ? 'administrator/' : '';

            // add language files
            langs.forEach(function (lang) {
                copy.language.files.push({
                    expand: true,
                    cwd: '<%= meta.tempPath %>/<%= meta.languagePath %>/' + adminDir + 'language/' + lang + '/',
                    src: [lang + langfileBase + '.ini', lang + langfileBase + '.sys.ini'],
                    dest: dir + '/language/' + lang + '/'
                });
            });
            // set the compress config for this module
            compress[module] = {
                options: {
                    archive: '<%= meta.buildPath %>/modules/' + module + '_<%= pkg.version %>.zip',
                    mode: 'zip'
                },
                files: [
                    {
                        expand: true,
                        cwd: dir,
                        src: ['**'],
                        dest: ''
                    }
                ]
            };
        });

        // save the new configs
        grunt.config.set('compress', compress);
        grunt.config.set('copy', copy);
    });

};