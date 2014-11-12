Feature: Adding Document Nodes
  As an API user of the history
  I expect that adding a document node triggers history updates

  Background:
    Given I have the following nodes:
      | Identifier                           | Path           | Node Type                  | Properties        | Workspace |
      | ecf40ad1-3119-0a43-d02e-55f8b5aa3c70 | /sites         | unstructured               |                   | live      |
      | fd5ba6e1-4313-b145-1004-dad2f1173a35 | /sites/typo3cr | TYPO3.TYPO3CR.Testing:Page | {"title": "Home"} | live      |
    And I have an empty history

  @fixtures
  Scenario: Add a new node to live workspace (e.g. in an API)
    Given I create the following nodes:
      | Identifier                           | Path                    | Node Type                  | Properties            | Workspace |
      | 75a28524-6a48-11e4-bd7d-7831c1d118bc | /sites/typo3cr/features | TYPO3.TYPO3CR.Testing:Page | {"title": "Features"} | live      |
    Then I should have the following history entries:
      | ID | Event Type   | Node Identifier                      | Document Node Identifier             | Workspace | Parent Event |
      | p  | NODE_ADDED   | 75a28524-6a48-11e4-bd7d-7831c1d118bc | 75a28524-6a48-11e4-bd7d-7831c1d118bc | live      |              |
      |    | NODE_ADDED   |                                      | 75a28524-6a48-11e4-bd7d-7831c1d118bc | live      | p            |
      |    | NODE_UPDATED | 75a28524-6a48-11e4-bd7d-7831c1d118bc | 75a28524-6a48-11e4-bd7d-7831c1d118bc | live      |              |