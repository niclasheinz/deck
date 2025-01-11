use OCP\DB\ISchemaWrapper;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version1000Date20250111101500 extends SimpleMigrationStep {

    // This method defines the changes to be made to the database schema
    public function changeSchema(IOutput $output, ISchemaWrapper $schema, array $options) {
        $table = $schema->getTable('oc_deck_cards');
        
        // Add a new column 'startdate' of type 'datetime' to the table
        $table->addColumn('startdate', 'datetime', [
            'notnull' => false,
        ]);
        
        // Return the modified schema
        return $schema;
    }
}