<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

class isMerged {
    #Gets the XML Jira for a SAK
    function getJira($sak) {
        if (!$sak) {
            return "";
        }
        $data=array();
        $jiraserver = "http://jira.sakaiproject.org/si/jira.issueviews:issue-xml";
        #Do some validation on $sak variable or a try/catch?
        $jira = @file_get_contents("$jiraserver/$sak/$sak.xml");

        if (strpos($jira,"<html")!==FALSE) {
            $xml = new SimpleXMLElement("<xml><channel><item><resolution>Security Issue</resolution></item></channel></xml>");
        }
        else if($jira) {
            $xml = new SimpleXMLElement($jira);
        }
        return $xml;
    }
}

$branchescmd = `git branch -v | awk {'print $1'}`;
$branches = explode("\n",$branchescmd);
$isMerged = new isMerged();
foreach ($branches as $branch) {
    preg_match('/(\w*-\d*)/',$branch,$matches);
    $jira = $matches[1];
    $item = ($isMerged->getJira($jira));

    $res = $item->channel->item->resolution;
    if ($res) {
        print "$branch : $res\n";
        if ($res == "Fixed") {
            $deletions .= "$branch ";
        }
    }
    else {
        print "$branch : Skipped, not in JIRA\n";
    }
}

print "Run this to delete all Fixed branches remove the --dry-run to really do it. Review the rest.\ngit branch -D $deletions;git push --prune --all --dry-run";
?>
