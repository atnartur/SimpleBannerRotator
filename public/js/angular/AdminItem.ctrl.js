app.controller('AdminItemCtrl', function ($scope, $http) {
    $scope.category = 0;
    $scope.options = [];
    $scope.recommendedOptions = [];
    $scope.availableOptions = [];

    $http.get('/admin/ajax/get_all_options').success(function(r) {
        $scope.availableOptions = r;
    });

    $scope.addOption = function(option) {
        $('#optionsModal').modal('hide');
        if((option.item_value_type == 'single_select' || option.item_value_type == 'multiple_select') && option.select_option_values == undefined)
            $http.get('/admin/ajax/get_select_values_for_option', {params: {option: option.id}}).success(function(r) {
                option.select_option_values = r;
                $scope.options.push(option);
            });
        else
            $scope.options.push(option);
    };

    $scope.removeOption = function(option) {
        $scope.options.splice($scope.options.indexOf(option), 1);
    };

    $scope.filterOptions = function(option) {
        var foundOptions = $.grep($scope.options, function(e){ return e.id == option.id; });
        return foundOptions.length <= 0;
    };

    $scope.getRecommendedOptions = function(catId) {
        $http.get('/admin/ajax/get_recommended_options', {params: {category: catId}}).success(function(r) {
            $scope.recommendedOptions = r;
        });
    };

    $scope.submit = function() {
        $('form[name="itemForm"]')[0].submit();
    };
});