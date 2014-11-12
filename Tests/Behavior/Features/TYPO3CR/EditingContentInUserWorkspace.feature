Feature: Editing Content in User Workspace
  As an API user of the history
  I expect that changing a node triggers history updates

  Background:
    Given I have the following nodes:
      | Identifier                           | Path           | Node Type                  | Properties        | Workspace |
      | ecf40ad1-3119-0a43-d02e-55f8b5aa3c70 | /sites         | unstructured               |                   | live      |
      | fd5ba6e1-4313-b145-1004-dad2f1173a35 | /sites/typo3cr | TYPO3.TYPO3CR.Testing:Page | {"title": "Home"} | live      |
    And I have an empty history

  @fixtures
  Scenario: Change a node property
    When I get a node by path "/sites/typo3cr" with the following context:
      | Workspace |
      | live      |
    And I set the node property "title" to "Homepage"
    Then I should have the following history entries:
      | Event Type   | Node Identifier                      | Document Node Identifier             | Workspace |
      | NODE_UPDATED | fd5ba6e1-4313-b145-1004-dad2f1173a35 | fd5ba6e1-4313-b145-1004-dad2f1173a35 | live      |
