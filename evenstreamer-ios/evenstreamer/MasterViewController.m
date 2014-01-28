//
//  MasterViewController.m
//  evenstreamer
//
//  Created by Stephan Lee on 1/23/14.
//  Copyright (c) 2014 Stephan Lee. All rights reserved.
//

#import "MasterViewController.h"
#import "EventPickerViewController.h"

@interface MasterViewController () <EventPickerDelegate> {
  NSMutableArray *_objects;
  NSMutableDictionary *cachedImages;
  
}
@end

@implementation MasterViewController
@synthesize cachedImages;

- (void)awakeFromNib
{
  [super awakeFromNib];
}

- (void)viewDidLoad
{
  [super viewDidLoad];
	
  if (self.event == nil) {
//    [self.delegate movePanelRight:YES];
    [self performSegueWithIdentifier:@"ShowEventList" sender:self];
  }
  [self.refreshControl addTarget:self action:@selector(refreshView:) forControlEvents:UIControlEventValueChanged];
  self.cachedImages = [[NSMutableDictionary alloc] init];
}

// This will get called too before the view appears
- (void)prepareForSegue:(UIStoryboardSegue *)segue sender:(id)sender
{
  if ([[segue identifier] isEqualToString:@"ShowEventList"]) {
  }
}

- (void)didReceiveMemoryWarning
{
  [super didReceiveMemoryWarning];
  // Dispose of any resources that can be recreated.
}

#pragma mark -
#pragma mark Drawer Navgiation
- (void) eventPicked:(NSString *)eventName {
  self.event = eventName;
  [self.delegate movePanelToOriginalPosition];
  // refresh
}

#pragma mark -
#pragma mark Drawer Navgiation
- (void)refreshView:(UIRefreshControl *)refresh {
  [self.refreshControl endRefreshing];
}

#pragma mark - model and stuff

- (void)insertNewObject:(id)sender
{
  if (!_objects) {
    _objects = [[NSMutableArray alloc] init];
  }
  [_objects insertObject:[NSDate date] atIndex:0];
  NSIndexPath *indexPath = [NSIndexPath indexPathForRow:0 inSection:0];
  [self.tableView insertRowsAtIndexPaths:@[indexPath] withRowAnimation:UITableViewRowAnimationAutomatic];
}

#pragma mark - Table View

- (NSInteger)numberOfSectionsInTableView:(UITableView *)tableView
{
  return 1;
}

- (NSInteger)tableView:(UITableView *)tableView numberOfRowsInSection:(NSInteger)section
{
  return 3;
}

- (UITableViewCell *)tableView:(UITableView *)tableView cellForRowAtIndexPath:(NSIndexPath *)indexPath
{
  UITableViewCell *cell = [tableView dequeueReusableCellWithIdentifier:@"Cell" forIndexPath:indexPath];
  
  UIImageView *imageView = (UIImageView *)[cell.contentView viewWithTag:1];
  
  NSString *identifier = [NSString stringWithFormat:@"Cell%d", indexPath.row];
  
  NSString *imageURL = @"http://lorempixel.com/640/480/";
  
  if ([self.cachedImages valueForKey:identifier] != nil) {
    imageView.image = [self.cachedImages valueForKey:identifier];
  } else {
    dispatch_async(dispatch_get_global_queue(DISPATCH_QUEUE_PRIORITY_DEFAULT, 0), ^{
      UIImage *img = nil;
      NSData *data = [[NSData alloc] initWithContentsOfURL:[NSURL URLWithString:imageURL]];
      img = [[UIImage alloc] initWithData:data];
      
      dispatch_async(dispatch_get_main_queue(), ^{
        if ([tableView indexPathForCell:cell].row == indexPath.row) {
          [self.cachedImages setValue:img forKey:identifier];
          imageView.image = [self.cachedImages valueForKey:identifier];
        }
      });
    });
  }
  
  UILabel *timestampView = (UILabel *)[cell.contentView viewWithTag:2];
  UILabel *usernameView = (UILabel *)[cell.contentView viewWithTag:3];
  
  NSDateFormatter *ndf = [[NSDateFormatter alloc] init];
  [ndf setDateStyle:NSDateFormatterShortStyle];
  [ndf setTimeStyle:NSDateFormatterNoStyle];
  timestampView.text = [ndf stringFromDate:[NSDate date]];
  
  usernameView.text = @"Stephan Lee";
  return cell;
}


- (BOOL)tableView:(UITableView *)tableView canEditRowAtIndexPath:(NSIndexPath *)indexPath
{
  // Return NO if you do not want the specified item to be editable.
  return NO;
}


-(CGFloat)tableView:(UITableView *)tableView estimatedHeightForRowAtIndexPath:(NSIndexPath *)indexPath {
  return 240.f;
}
-(CGFloat)tableView:(UITableView *)tableView heightForRowAtIndexPath:(NSIndexPath *)indexPath {
  return 240.f;
}

- (void) onDimiss:(NSString *)eventName {
  self.event = eventName;
}

#pragma mark - Nav stuff

- (IBAction)onClick:(id)sender {
  [self.delegate togglePanelRight];
}



@end
