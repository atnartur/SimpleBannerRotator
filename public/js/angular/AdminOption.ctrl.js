app.controller('AdminOptionCtrl', function ($scope) {
    console.log(222)
    $scope.AvilableBannerValueTypes = [
        {id:'range', name: 'Диапазон'},
        {id:'specific', name: 'Точное значение'},
        {id:'multiple_select', name: 'Множественный выбор'},
        {id:'single_select', name: 'Единичный выбор'}
    ];

    $scope.bannerValueTypes = [];

    $scope.selectValues = [];
    $scope.messages = [];

    $scope.submit = function() {
        if(($scope.filterType == 'multiple_select' || $scope.filterType == 'single_select') && $scope.selectValues.length == 0) {
            $scope.messages.push({type: 'danger', text: 'При использовании параметров множественного или единичного выбора необходимо задать набор допустимых значений'});
            return false;
        }
        $('form[name="optionForm"]')[0].submit();
    }

    $scope.rebuildBannerValueTypes = function() {
        $scope.bannerValueType = undefined;
        $scope.bannerValueTypes = [];
        if($scope.filterType == 'range') {
            $scope.bannerValueTypes.push($scope.AvilableBannerValueTypes[0]);
            $scope.bannerValueTypes.push($scope.AvilableBannerValueTypes[1]);
        }
        else if($scope.filterType == 'multiple_select') {
            $scope.bannerValueTypes.push($scope.AvilableBannerValueTypes[2]);
            $scope.bannerValueTypes.push($scope.AvilableBannerValueTypes[3]);
        }
        else if($scope.filterType == 'single_select') {
            $scope.bannerValueTypes.push($scope.AvilableBannerValueTypes[3]);
        }
    };

    $scope.removeSelectValueWithConfirm = function(index) {
        if($scope.selectValues[index].new == undefined)
            bootbox.dialog({
                message: "<p>Вы уверены что хотите удалить данное значение? Возможно оно используется товарами. Перед удалением убедитесь в обратном.</p><p class=\"text-muted\">Примечание: все изменения будут применены после нажатия кнопки \"Сохранить\"</p>",
                title: "Удаление значения параметра",
                buttons: {
                    cancel: {
                        label: "Отмена",
                        className: "btn-default",
                        callback: function() {

                        }
                    },
                    ok: {
                        label: "Удалить",
                        className: "btn-danger",
                        callback: function() {
                            $scope.$apply(function () {
                                $scope.selectValues.splice(index, 1);
                            });
                        }
                    }
                }
            });
        else
            $scope.selectValues.splice(index, 1);
    }
});