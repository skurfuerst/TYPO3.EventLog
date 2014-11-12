Feature: Publish user workspace
  As an API user of the history
  I expect that publishing a content collection triggers content changed events.

  Background:
    Given I have the following nodes:
      | Identifier                           | Path                 | Node Type                  | Properties        | Workspace |
      | ecf40ad1-3119-0a43-d02e-55f8b5aa3c70 | /sites               | unstructured               |                   | live      |
      | fd5ba6e1-4313-b145-1004-dad2f1173a35 | /sites/typo3cr       | TYPO3.TYPO3CR.Testing:Page | {"title": "Home"} | live      |
    And I have an empty history


  @fixtures
  Scenario: Publish a new ContentCollection with Content
    When I create the following nodes:
      | Path                                          | Node Type                               | Properties              | Workspace |
      | /sites/typo3cr/main/twocol                    | TYPO3.TYPO3CR.Testing:TwoColumn         | {}                      | user-demo |
      | /sites/typo3cr/main/twocol/column0/text       | TYPO3.TYPO3CR.Testing:Text              | {"text": "Hello world"} | user-demo |
    And I publish the workspace "user-demo"
    Then I should have the following history entries.