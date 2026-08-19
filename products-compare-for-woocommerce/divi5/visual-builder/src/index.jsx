import { addAction } from '@wordpress/hooks';
import { registerModule } from '@divi/module-library';
import { createCompareModule } from './module-factory';
import compareWidgetMetadata from './modules/compare-widget/module.json';
import compareButtonMetadata from './modules/compare-button/module.json';
import compareTableMetadata from './modules/compare-table/module.json';

addAction('divi.moduleLibrary.registerModuleLibraryStore.after', 'brcmp.divi5Modules', () => {
  [
    createCompareModule(compareWidgetMetadata, ['compareButton', 'productLabel']),
    createCompareModule(compareButtonMetadata, ['button', 'checkMark']),
    createCompareModule(compareTableMetadata, ['productTitle', 'productPrice', 'attributeName', 'attributeValue']),
  ].forEach((module) => {
    const { metadata, ...moduleConfig } = module;
    registerModule(metadata, moduleConfig);
  });
});
