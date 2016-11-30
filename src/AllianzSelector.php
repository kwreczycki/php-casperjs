<?php

final class AllianzSelector {
    const SELL_CAR_BTN = '//*[@id="landing-page"]/div[2]/div/div[1]/div/div/div/button';
    const MAKE_SELECTOR = '/html[@class="no-js ng-scope"]/body[@class="modal-open"]/div[@class="modal fade ng-isolate-scope in"]/div[@class="modal-dialog modal-lg"]/div[@class="modal-content"]/form[@class="form-horizontal ng-scope ng-invalid ng-invalid-required ng-dirty"]/div[@class="modal-body"]/a2-vehicle-form/div[@class="form-horizontal ng-invalid ng-invalid-required ng-dirty"]/div[@class="row"]/div[@class="col-md-6"][2]/div[@class="form-group ng-isolate-scope"][1]/div[@class="col-sm-7"]/div[@class="field-disabled-click-pass-fix ng-scope tooltip-wrap"]/div[1]/div[@class="selectize-control ng-scope ng-isolate-scope ng-pristine ng-valid single"]/div[@class="selectize-input items not-full has-options"]';
    const MARK_SELECTOR = '//div[@class="col-md-6"][2]/div[@class="form-group ng-isolate-scope"][2]/div[@class="col-sm-7"]/div[@class="field-disabled-click-pass-fix ng-scope tooltip-wrap"]/div[1]/div[@class="selectize-control ng-scope ng-isolate-scope ng-pristine ng-valid single"]/div[@class="selectize-input items not-full"]';
    const VERSION_SELECTOR = '//div[@class="col-md-6"][2]/div[@class="form-group ng-isolate-scope"][3]/div[@class="col-sm-7"]/div[@class="field-disabled-click-pass-fix ng-scope tooltip-wrap"]/div[1]/div[@class="selectize-control ng-scope ng-isolate-scope ng-pristine ng-valid single"]/div[@class="selectize-input items has-options full has-items"]';
    const SAVE_BTN = '//div[@class="modal-footer ng-scope"]/button[@class="btn btn-default ng-binding ng-scope"]';
    const SAVE_BASIC_DATA_BTN = '/html/body/div[7]/div[2]/div/div[3]/button[3]';
    const OPEN_OC_DATA_BTN = '//*[@id="panel-przedmiot-glowny"]/div[2]/div/form/button[2]';
    const FIRST_NAME_FIELD = '/html[@class="no-js ng-scope"]/body[@class="modal-open"]/div[@class="modal fade ng-isolate-scope modal-vehicleowners in"]/div[@class="modal-dialog"]/div[@class="modal-content"]/div[@class="modal-body ng-scope"]/ng-form[@class="form-horizontal ng-pristine ng-valid ng-valid-required"]/a2-vehicle-owner-form[@id="owner-user-0"]/div[@class="panel panelVehicleOwners"]/div[@class="panel-body"]/form[@class="form-horizontal ng-pristine ng-valid ng-valid-required"]/div[@class="subcomponent-header ng-isolate-scope"]/div[@class="collapse in"]/div/div[@class="form-group ng-isolate-scope collapse in"][3]/div[@class="col-sm-7"]/div[@class="field-disabled-click-pass-fix ng-scope tooltip-wrap"]/div[1]/input[@class="ng-scope ng-pristine ng-valid form-control ng-valid-format ng-valid-maxlength ng-valid-minlength ng-valid-pattern ng-valid-required"]';
    const REGISTRATION_NUMBER = '//*[@id="regNo2"]';
    const PRODUCTION_YEAR = '//*[@id="prodYear"]';
    const PESEL = '//*[@id="ufgIdNo"]';
    const FIRSTNAME = '//input[@name="firstName"]';
    const LASTNAME = '//input[@name="lastName"]';
    const REGISTRATION_NUMBER_XPATH = '//*[@id="regNo2"]';
    const PRODUCTION_YEAR_XPATH = '//*[@id="prodYear"]';
    const PESEL_XPATH = '//*[@id="ufgIdNo"]';
    const FIRSTNAME_XPATH = '//input[@name="firstName"]';
    const LASTNAME_XPATH = '//input[@name="lastName"]';
    const MARK_SELECTOR_XPATH = '/html/body/div[7]/div[2]/div/form/div/a2-vehicle-form/div/div/div[2]/div[1]/div/div/div[1]/div/div[1]/input';
    const MAKE_SELECTOR_XPATH = '/html/body/div[7]/div[2]/div/form/div/a2-vehicle-form/div/div/div[2]/div[2]/div/div/div[1]/div/div[1]/input';
    const VERSION_SELECTOR_XPATH = '/html/body/div[7]/div[2]/div/form/div/a2-vehicle-form/div/div/div[2]/div[3]/div/div/div[1]/div/div[1]/input';
    const CAPACITY_XPATH = '//*[@id="carryingCapacity"]';
    const FIRST_REGISTRATION_DATE_XPATH = '//*[@id="firstRegistrationDate"]';
    const SAVE_OC_DATA_BTN_XPATH = '//html/body/div[7]/div[2]/div/div[2]/button[2]';
}
