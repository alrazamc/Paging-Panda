"use strict";
angular.module('fileUpload', [])
    .directive('fileUpload', ['$timeout', '$http', function ($timeout, $http) {
        return {
            restrict: 'E',
            template: '<div ng-transclude></div>',
            replace: true,
            transclude: true,
            scope: {
                headers: '=',
                ngModel: '=',
                disabled: '='
            },
            require: 'ngModel',
            link: function (scope, el, attr) {
                var fileName,
                    shareCredentials,
                    withPreview,
                    fileSelector,
                    resize,
                    limit,
                    size_limit,
                    maxWidth,
                    maxHeight,
                    accept,
                    files_data,
                    sel;

                fileName = attr.name || 'userFile';
                shareCredentials = attr.credentials === 'true';
                withPreview = attr.preview === 'true';
                resize = attr.resize === 'true';
                limit = angular.isDefined(attr.limit) ? parseInt(attr.limit) : false;
                size_limit = angular.isDefined(attr.size) ? parseInt(attr.size) : false;
                accept = angular.isDefined(attr.accept) ? attr.accept : '';
                maxWidth = angular.isDefined(attr.maxWidth) ? parseInt(attr.maxWidth) : false;
                maxHeight = angular.isDefined(attr.maxHeight) ? parseInt(attr.maxHeight) : false;
                fileSelector = angular.isDefined(attr.fileSelector) ? attr.fileSelector : false;
                files_data = {};
                el.append('<input style="display: none !important;" type="file" ' + (attr.multiple == 'true' ? 'multiple' : '') + ' accept="' + (attr.accept ? attr.accept : '') + '" name="' + fileName + '"/>');

                function Resize(file, index, type) {
                    var canvas = document.createElement("canvas");
                    var img = document.createElement("img");
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        img.src = e.target.result;
                        draw();
                    };
                    reader.readAsDataURL(file);

                    function b64toBlob(b64Data, contentType, sliceSize) {
                        contentType = contentType || '';
                        sliceSize = sliceSize || 512;

                        var byteCharacters = atob(b64Data);
                        var byteArrays = [];

                        for (var offset = 0; offset < byteCharacters.length; offset += sliceSize) {
                            var slice = byteCharacters.slice(offset, offset + sliceSize);

                            var byteNumbers = new Array(slice.length);
                            for (var i = 0; i < slice.length; i++) {
                                byteNumbers[i] = slice.charCodeAt(i);
                            }

                            var byteArray = new Uint8Array(byteNumbers);

                            byteArrays.push(byteArray);
                        }

                        var blob = new Blob(byteArrays, {type: contentType});
                        return blob;
                    }

                    function draw() {
                        var width = img.width;
                        var height = img.height;
                        var ctx = canvas.getContext("2d");
                        ctx.drawImage(img, 0, 0);

                        if (width > 0 && height > 0) {
                            if (width > height) {
                                if (width > maxWidth) {
                                    height *= maxWidth / width;
                                    width = maxWidth;
                                }
                            } else {
                                if (height > maxHeight) {
                                    width *= maxHeight / height;
                                    height = maxHeight;
                                }
                            }

                            canvas.width = width;
                            canvas.height = height;
                            ctx.drawImage(img, 0, 0, width, height);
                            var b64 = canvas.toDataURL(type).split(',')[1];
                            file = b64toBlob(b64, type, 512);
                        }

                        uploadFile(file, index);
                    }
                }

                function upload(fileProperties, index, file) {
                    if (resize && maxWidth && maxHeight && (file.type.indexOf('image/') !== -1)) {
                        Resize(file, index, file.type);
                    } else {
                        uploadFile(file, index);
                    }
                    return angular.extend(scope.ngModel[index], {
                        name: fileProperties.name,
                        size: fileProperties.size,
                        type: fileProperties.type,
                        status: {},
                        percent: 0,
                        preview: null
                    });
                }

                function uploadFile() {
                    var index = false;
                    for(var i = 0; i < scope.ngModel.length; i++)
                    {
                        if(typeof scope.ngModel[i].sgnurl !== 'undefined' && typeof scope.ngModel[i].uploaded === 'undefined')
                        {
                            index = i;
                            break;
                        }
                    }
                    if(index === false){
                        scope.$apply();
                        return;
                    }
                    scope.ngModel[index].uploaded = false;
                    var xhr = new XMLHttpRequest(),
                    progress = 0,
                    uri = scope.ngModel[index].sgnurl;
                    xhr.open('PUT', uri, true);
                    xhr.withCredentials = shareCredentials;
                    if (scope.headers) {
                        scope.headers.forEach(function (item) {
                            xhr.setRequestHeader(item.header, item.value);
                        });
                    }
                    xhr.onreadystatechange = function () {
                        scope.ngModel[index].status = {
                            code: xhr.status,
                            statusText: xhr.statusText,
                            response: xhr.response
                        };
                        scope.$apply();
                        if (this.readyState == 4 && this.status == 200) {
                            scope.ngModel[index].uploaded = true;
                            uploadFile();
                        }
                    };
                    xhr.upload.addEventListener("progress", function (e) {
                        progress = parseInt(e.loaded / e.total * 100);
                        scope.ngModel[index].percent = progress;
                        scope.$apply();
                    }, false);
                    xhr.send(files_data[scope.ngModel[index].data_file]);
                }

                function loadFilePreview(file, index) {
                    if (withPreview) {
                        var reader = new FileReader();
                        reader.onload = function (e) {
                            scope.ngModel[index].preview = e.target.result;
                            scope.$apply();
                        };
                        reader.readAsDataURL(file);
                    }
                }

                function makeid() {
                      var text = "";
                      var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

                      for (var i = 0; i < 5; i++)
                        text += possible.charAt(Math.floor(Math.random() * possible.length));

                      return text;
                }
                function requestUpload()
                {
                    var upload = [];
                    for(var i = 0; i < scope.ngModel.length; i++)
                    {
                        if(typeof scope.ngModel[i].sgnurl === 'undefined' && (size_limit === false || scope.ngModel[i].size < size_limit))
                            upload.push({
                                index : i,
                                name: scope.ngModel[i].name,
                                type: scope.ngModel[i].type
                            });
                    }
                    if(upload.length == 0) return;
                    $http.post(attr.uri, {files: upload}).then(function(response){
                        for(var i = 0; i < response.data.length; i++)
                        {
                            if(scope.ngModel[response.data[i].index]){
                                scope.ngModel[response.data[i].index].key =  response.data[i].file_name;
                                scope.ngModel[response.data[i].index].sgnurl =  response.data[i].sgnurl;
                            }
                        }
                       uploadFile();
                    }, function(){});
                }

                $timeout(function () {
                    sel = fileSelector ? angular.element(el[0].querySelectorAll(fileSelector)[0]) : el;
                    sel.bind('click', function () {
                        if (!scope.disabled) {
                            scope.$eval(el.find('input')[0].click());
                        }
                    });
                });

                angular.element(el.find('input')[0]).bind('change', function (e) {
                    var files = e.target.files;
                    if (!angular.isDefined(scope.ngModel)) {
                        scope.ngModel = [];
                    }
                    var f;
                    for (var i = 0; i < files.length; i++) {
                        if(limit && scope.ngModel.length == limit)
                            continue;
                        if(accept.indexOf(files[i].type) === -1 )
                            continue;
                        f = {
                            name: files[i].name,
                            size: files[i].size,
                            type: files[i].type,
                            data_file: makeid(),
                            status: {},
                            percent: 0,
                            preview: null
                        };
                        if(f.type.indexOf('video/mp4') !== -1)
                        {
                            f.object_url = URL.createObjectURL(files[i]);
                        }
                        files_data[f.data_file] = files[i];
                        scope.ngModel.push(f);
                        loadFilePreview(files[i], scope.ngModel.length - 1);
                    }
                    e.target.value = '';
                    scope.$apply();
                    requestUpload();
                })
            }
        }
    }]);
