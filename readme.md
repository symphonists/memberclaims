# Member Claims

- Status: **Untested**
- Version: 0.2 beta
- Author: Craig Zheng
- Build Date: 14 Sep 2011
- Requirements:
	- [Symphony 2.2](https://github.com/symphonycms/symphony-2) or higher
	- [Members 1.0](https://github.com/symphonycms/members) or higher
	
## Description

A Symphony extension that allows unique Member-to-Entry actions. Useful for developing functionalities like following and voting, or things like "I use this" or "I have this question too".

### Features

- Provides a field type allowing any number of "Member Claim" fields to be added to your sections
- Provides an event used to create/remove claims for the currently-logged-in member
- Allows filtering entries by a claimant's member id (e.g. "only entries that I follow")
- Allows sorting entries by claim count (e.g. "sort by number of votes")

## Installation

Information about [installing and updating extensions](http://symphony-cms.com/learn/tasks/view/install-an-extension/) can be found in the Symphony documentation at <http://symphony-cms.com/learn/>.

## Usage

### Event

See the event's own documentation after installing.

### Data Source Output

There are two output modes. The default mode simply outputs an empty element with attributes for the claim count, the field id, and a flag for whether the currently-logged-in member is a claimant for the entry. For example:

            <followers count="13" field-id="43" current-member="Yes" />

The list mode, on the other hand, outputs the ids of all claimant members:

            <followers count="3" field-id="43" current-member="Yes">
                <item>3</item>
                <item>6</item>
                <item>21</item>
            </users>

_The field id is included in the output because it's required by the event_