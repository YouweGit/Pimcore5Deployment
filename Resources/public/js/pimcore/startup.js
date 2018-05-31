pimcore.registerNS("pimcore.plugin.Pimcore5DeploymentBundle");

pimcore.plugin.Pimcore5DeploymentBundle = Class.create(pimcore.plugin.admin, {
    getClassName: function () {
        return "pimcore.plugin.Pimcore5DeploymentBundle";
    },

    initialize: function () {
        pimcore.plugin.broker.registerPlugin(this);
    },

    pimcoreReady: function (params, broker) {
        //alert("Pimcore5DeploymentBundle ready!");
    }
});

var Pimcore5DeploymentBundlePlugin = new pimcore.plugin.Pimcore5DeploymentBundle();
