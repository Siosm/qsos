#!/usr/bin/perl -w

use strict;

use Getopt::Long;

sub getLanguage {
  my $file = shift;

  my $filenamelang = 'en';
  my $insheetlang = '';

  if ($file =~ /_(\S\S)\.qtpl/) {
    $filenamelang = $1;
  }

  open FILE,"<$file" or die "Can't open $file: $!";
  foreach (<FILE>) {
    if (/<language>(\S+)<\/language>/) {
      $insheetlang = $1;
    }
  }
  close $file;

  if ($filenamelang ne $insheetlang) {
    die "sheet name doesn't mach the language set in the sheet <language>$insheetlang</language>";
  }

  return $insheetlang;
}

my $includedir = "../../sheet/include/";
my $qtpl;

GetOptions (
  'include=s' => \$includedir,
  'qtpl=s' => \$qtpl,
);

sub usage {
  my $msg = shift;
  print STDERR $msg."\n" if $msg;

  print STDERR "usage:\n";
  print STDERR "  createemptysheet --include=/includepath --qtpl qsos-template.qtpl:\n";
  print STDERR " Default include directory is $includedir\n";
  exit 1;
}

usage("Missing argument") if ! (defined $includedir && defined $qtpl);
usage("Can't find include directory") if ! -d $includedir;
usage("Can't find template file") if ! -f $qtpl;
my $lang = getLanguage($qtpl);
my @buff;
open QTPL,"<$qtpl" or die "Failed to open $qtpl: $?";
@buff = <QTPL>;
close QTPL;

while (my $line = shift @buff) {
  if ($line =~ /<include\W+section="([-\w]+)"\W*(|\/)>/) { # this is an include
    if ($lang ne 'en' && -f "$includedir/$1_$lang.qin" ) {
      open INCLUDE, "<$includedir/$1_$lang.qin" or die "Failed to open ".
      "localised includefile"
    } else {
      open INCLUDE, "<$includedir/$1.qin" or die "Failed to open includefile"
    }
    unshift @buff, <INCLUDE>;
    close INCLUDE;
  } else {
    print $line;
  }
}
