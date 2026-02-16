Arxiu Plugin for AtoM
====

<!-- TOC -->
* [Arxiu Plugin for AtoM](#arxiu-plugin-for-atom)
* [Introduction](#introduction)
* [Duplicate hierarchy detection](#duplicate-hierarchy-detection)
  * [Command-line interface](#command-line-interface)
* [Report feature](#report-feature)
  * [input parameter](#input-parameter)
  * [Report output](#report-output)
* [Move descriptions from one term to another](#move-descriptions-from-one-term-to-another)
  * [Command-line interface](#command-line-interface-1)
<!-- TOC -->

Introduction
==
The Arxiu Plugin for AtoM is a plugin that provides additional features and improvements to the AtoM archival management system. The plugin includes the following features:
- duplicate hierarchy detection: A tool to identify and manage duplicate hierarchies within the AtoM system, helping to maintain data integrity and organization. Subject hierarchies and place hierarchies are included in this feature.
- duplicate authority record detection: A tool to identify and manage duplicate authority records, ensuring accurate and consistent metadata.


Duplicate hierarchy detection
==
The duplicate hierarchy detection feature allows users to identify and manage duplicate hierarchies within the AtoM system. This helps to maintain data integrity and organization. The feature includes the following functionalities:
- Detection of duplicate subject hierarchies: The plugin can identify duplicate subject hierarchies based on their names, allowing users to review and manage them effectively.
- detection of duplicate place hierarchies: The plugin can also identify duplicate place hierarchies, ensuring that the geographical information in the AtoM system is accurate and well-organized.
- Management of duplicates: Once duplicates are identified, users can review them and take appropriate actions, such as merging or deleting duplicate records, to maintain a clean and organized hierarchy structure in AtoM.
- The duplicate hierarchy detection feature is designed to enhance the overall data quality and usability of the AtoM system by ensuring that hierarchies are unique and well-maintained.
- Hierarchies are considered duplicates if they have the same name, regardless of their position in the hierarchy or other attributes. This allows for a straightforward identification of duplicates based on their names.
- The plugin provides a user-friendly interface for reviewing and managing duplicate hierarchies, making it easier for users to maintain the integrity of their data in AtoM.
- Hierarchy terms in different languages are treated as separate entities, even if they have the same name, to accommodate multilingual data and ensure that duplicates are identified based on the specific language context.
- A dry-run feature is included to allow users to see potential duplicates without making any changes to the data, providing a safe way to review duplicates before taking action.
- A report option will be available to generate a report of identified duplicates, which can be useful for documentation and further analysis.
- The duplicate hierarchy detection feature is designed to be efficient and scalable, allowing it to handle large datasets without significant performance issues.
- It is implemented as a command-line tool, making it accessible for users with varying levels of technical expertise and allowing for integration into existing workflows for data management in AtoM.
- There is also an integrated gearmand task for the report of duplicate hierarchy detection, allowing for automated and scheduled checks for duplicates in the AtoM system, ensuring ongoing data integrity and organization. The task will allow running the duplicate hierarchy detection process in the background, without requiring manual intervention, and can be configured to run at specific intervals to report on the status of duplicate hierarchies in the AtoM system. This helps to maintain a clean and organized hierarchy structure over time, ensuring that any new duplicates are identified and managed promptly.
- Using the report, the user can review the duplicates and decide on the appropriate actions, such as merging or deleting duplicate records, to maintain a clean and organized hierarchy structure in AtoM. The report will provide detailed information about the identified duplicates, including their names, locations in the hierarchy, and any relevant attributes, to assist users in making informed decisions about how to manage the duplicates effectively.

Command-line interface
--
The Arxiu Plugin for AtoM includes a command-line interface (CLI) that allows users to interact with the plugin's features directly from the terminal. The CLI provides a convenient way to access and manage the plugin's functionalities without needing to navigate through the AtoM web interface. The CLI includes the following commands:
- `detect-duplicates`: This command initiates the duplicate hierarchy detection process, allowing users to identify duplicate subject and place hierarchies in the AtoM system.
- `manage-duplicates`: This command provides options for managing identified duplicates, such as merging or deleting duplicate records, to maintain a clean and organized hierarchy structure in AtoM.
- `generate-report`: This command generates a report of identified duplicates, providing detailed information about the duplicates for review and analysis.
- `dry-run`: This is an option that allows users to perform a dry run of the duplicate detection process, showing potential duplicates without making any changes to the data, providing a safe way to review duplicates before taking action.
- `schedule-task`: This command allows users to schedule the duplicate hierarchy detection process to run automatically at specific intervals using the integrated gearmand task, ensuring ongoing data integrity and organization in the AtoM system.
- The CLI is designed to be user-friendly and accessible for users with varying levels of technical expertise, providing clear instructions and options for each command to facilitate effective use of the plugin's features. It also includes error handling and feedback mechanisms to guide users through the process of managing duplicates and maintaining the integrity of their data in AtoM. The CLI commands can be executed from the terminal, allowing for efficient and streamlined management of duplicate hierarchies in the AtoM system. The CLI also supports additional options and parameters for each command, providing flexibility and customization for users to tailor the duplicate detection and management process to their specific needs and workflows in AtoM. Overall, the command-line interface of the Arxiu Plugin for AtoM enhances the usability and accessibility of the plugin's features, making it easier for users to maintain a clean and organized hierarchy structure in the AtoM system and ensuring ongoing data integrity and organization.
- it is implemted using the Symfony Console component, which provides a robust framework for building command-line applications in PHP. This allows for a structured and efficient implementation of the CLI, with support for various features such as input validation, command options, and output formatting, enhancing the overall user experience when interacting with the plugin's functionalities through the terminal.

Example usage of the CLI integrated into AtoM:
```
# To detect duplicate hierarchies
php symphony arxiu:detect-duplicates
# To manage identified duplicates
php symphony arxiu:manage-duplicates
# To generate a report of identified duplicates
php symphony arxiu:generate-report
# To perform a dry run of the duplicate detection process
php symphony arxiu:detect-duplicates --dry-run
# To schedule the duplicate hierarchy detection process to run automatically at specific intervals
php symphony arxiu:schedule-task --interval=weekly
```

Report feature
==
The Arxiu Plugin for AtoM includes a reporting feature that allows users to generate detailed reports of identified duplicates in the AtoM system. The report provides comprehensive information about the duplicates, including their names, locations in the hierarchy, and any relevant attributes, to assist users in reviewing and managing duplicates effectively. The report can be generated using the CLI command `generate-report`, and it includes the following features:
- Detailed information: The report provides detailed information about each identified duplicate, including its name, location in the hierarchy, and any relevant attributes, to help users understand the context of the duplicates and make informed decisions about how to manage them.
- Export options: The report can be exported in various formats, such as CSV or PDF, allowing users to easily share and analyze the information about the duplicates with colleagues or for documentation purposes.

input parameter
--

The report uses as input the identifier of a hierarchy element. The report will include all the duplicates of that hierarchy element, as well as their location in the hierarchy and any relevant attributes. This allows users to review the duplicates in the context of their position in the hierarchy and make informed decisions about how to manage them effectively. The report can be generated for specific hierarchy elements, allowing users to focus on particular areas of interest or concern within the AtoM system when reviewing duplicates. Overall, the reporting feature of the Arxiu Plugin for AtoM provides valuable insights into identified duplicates, helping users maintain a clean and organized hierarchy structure in the AtoM system and ensuring ongoing data integrity and organization.

this is an example of the input parameter for generating a report of identified duplicates in the AtoM system using the Arxiu Plugin:
```# To generate a report for a specific hierarchy element with identifier "12345"
php symphony arxiu:generate-report --term-id=12345
```

Report output
--
This is an example of the report generated for a specific hierarchy element, it also shows the number of documents that use the element as subject, and the number of documents that use the element as place. 

It will include the identifier and culture of the element, as well as its name, location in the hierarchy and any relevant attributes. The report will also include a list of all the duplicates of that hierarchy element, with their own identifier, culture, name, location in the hierarchy and attributes. This allows users to review the duplicates in detail and make informed decisions about how to manage them effectively.

```
Duplicate Hierarchy Report
==========================

Primary Element:
  ID: 12345
  Culture: en
  Name: "Anthropology"
  Location in Hierarchy: "Subject > Social Sciences > Anthropology"
  Number of Documents using as Subject: 15
  Number of Documents using as Place: 0

Duplicate Records:
  1. ID: 12398
     Culture: en
     Name: "Anthropology"
     Location in Hierarchy: "Subject > Humanities > Anthropology"
     Number of Documents using as Subject: 8
     Number of Documents using as Place: 0

  2. ID: 12567
     Culture: en
     Name: "Anthropology"
     Location in Hierarchy: "Subject > Anthropology"
     Number of Documents using as Subject: 3
     Number of Documents using as Place: 0

  3. ID: 12891
     Culture: ca
     Name: "Antropologia"
     Location in Hierarchy: "Subject > Ciències Socials > Antropologia"
     Number of Documents using as Subject: 2
     Number of Documents using as Place: 0
```

Move descriptions from one term to another
==

The Arxiu Plugin for AtoM includes a feature that allows users to move descriptions from one term to another within the AtoM system. This feature is particularly useful for managing and organizing metadata, ensuring that descriptions are associated with the correct terms and improving the overall quality of the data in AtoM. The feature includes the following functionalities:
- Selection of source and target terms: Using a slug, users can select the source term from which descriptions will be moved, and the target term to which the descriptions will be transferred. This allows for flexibility in managing descriptions and ensuring that they are associated with the appropriate terms in the AtoM system.
- Description transfer: The feature facilitates the transfer of descriptions from the source term to the target term, ensuring that all relevant information is preserved and accurately associated with the new term. This helps to maintain the integrity of the metadata and ensures that descriptions are correctly linked to the appropriate terms in AtoM.
- User-friendly interface: The feature is designed to be user-friendly, providing clear instructions and options for selecting source and target terms, and for confirming the transfer of descriptions. This makes it accessible for users with varying levels of technical expertise, allowing them to manage descriptions effectively within the AtoM system.
- Error handling and feedback: The feature includes error handling mechanisms to ensure that any issues encountered during the description transfer process are properly managed and communicated to the user. Feedback is provided throughout the process to guide users and ensure that they are informed about the status of the transfer, helping to facilitate a smooth and efficient management of descriptions in AtoM. Overall, the ability to move descriptions from one term to another within the AtoM system enhances the flexibility and organization of metadata, allowing users to maintain accurate and well-structured descriptions that are correctly associated with the appropriate terms, ultimately improving the quality and usability of the data in AtoM.
- command-line interface: The feature is accessible through a command-line interface (CLI), allowing users to execute the description transfer process directly from the terminal. This provides a convenient and efficient way to manage descriptions without needing to navigate through the AtoM web interface, making it easier for users to maintain accurate and well-organized metadata in the AtoM system. The CLI includes options for selecting source and target terms, confirming the transfer of descriptions, and providing feedback on the status of the transfer process, ensuring a user-friendly experience when managing descriptions in AtoM.


This feature is based on the [Move description relations from one authority record to another](https://www.accesstomemory.org/en/docs/2.10/admin-manual/maintenance/cli-tools/#move-description-relations-from-one-authority-record-to-another)

For the implementation, you will also have a look at the [Export a list of terms linked to one or more descriptions from a taxonomy](https://www.accesstomemory.org/en/docs/2.10/admin-manual/maintenance/cli-tools/#export-a-list-of-terms-linked-to-one-or-more-descriptions-from-a-taxonomy)

Command-line interface
--
Command-line interface example usage:
```# To move descriptions from a source term with slug "source-term-slug" to a target term with slug "target-term-slug"
php symphony arxiu:move-descriptions --source-slug=source-term-slug --target-slug=target-term-slug
```

Command-line interface example output includes a description of the process, a list of all the descriptions moved, and a final report of the transfer, including the number of descriptions moved and received by each term, as well as the overall status of the transfer process. The output provides clear and concise information about the transfer, allowing users to understand the results of the operation and take any necessary actions based on the outcome.
```Description Transfer Report
==========================
Source Term:
  Slug: source-term-slug
  Name: "Source Term"
  Number of Descriptions Moved: 5
Target Term:
  Slug: target-term-slug
  Name: "Target Term"
  Number of Descriptions Received: 5
Transfer Status: Success
Details of Moved Descriptions:
  1. Description ID: 101
     Content: "Description content for description 101"
  2. Description ID: 102
     Content: "Description content for description 102"
  3. Description ID: 103
     Content: "Description content for description 103"
  4. Description ID: 104
     Content: "Description content for description 104"
  5. Description ID: 105
     Content: "Description content for description 105"
```

The user will also specify the culture of the descriptions to be moved, ensuring that only descriptions in the specified culture are transferred from the source term to the target term. This allows for more precise management of descriptions, particularly in multilingual contexts, ensuring that descriptions are correctly associated with the appropriate terms based on their cultural context in the AtoM system. The CLI command for moving descriptions will include an additional option for specifying the culture, as shown in the example usage below:
```# To move descriptions from a source term with slug "source-term-slug" to a target term with slug "target-term-slug" for a specific culture "en"
php symphony arxiu:move-descriptions --source-slug=source-term-slug --target-slug=target-term-slug --culture=en
```